<?php
namespace SoftanUsers;

final class Config
{
    private const XOR_KEY = 's0ft4n-sdk-2025';

    public static function getMeta(string $key, mixed $default = null): mixed
    {
        return SDK::$META[$key] ?? $default;
    }

    public static function getConfig(string $key, mixed $default = null): mixed
    {
        return SDK::$CONFIG[$key] ?? $default;
    }

    public static function getActiveEnvironment(): string
    {
        // 1. Programmatic override
        $active = self::getConfig('active_environment');
        if ($active) {
            return strtolower((string) $active);
        }

        // 2. Server / process environment variable
        $envVar = getenv('SOFTAN_USERS_ENV');
        if ($envVar !== false && $envVar !== '') {
            return strtolower((string) $envVar);
        }

        // 3. Package default (sdk_meta.json → default_environment)
        return (string) self::getMeta('default_environment', 'stg');
    }

    public static function getBaseUrl(?string $env = null): string
    {
        $env  = $env ?: self::getActiveEnvironment();
        $base = (string) (self::getMeta('base_urls', [])[$env] ?? '');
        if ($base && !str_ends_with($base, '/')) {
            $base .= '/';
        }
        return $base;
    }

    public static function getAppId(): string
    {
        return (string) (self::getMeta('app_id', ''));
    }

    public static function getApiKey(?string $env = null): string
    {
        $env     = $env ?: self::getActiveEnvironment();
        $encoded = (string) (self::getMeta('credentials', [])[$env]['api_key'] ?? '');
        return $encoded !== '' ? self::decodeKey($encoded) : '';
    }

    /**
     * Resolve an endpoint path from the nested endpoints map in sdk_meta.json.
     *
     * Usage: Config::resolveEndpoint('users', 'base')           → 'users'
     *        Config::resolveEndpoint('commons', 'countries')    → 'commons/countries'
     *
     * @throws \InvalidArgumentException if the path is not found or not a string leaf.
     */
    public static function resolveEndpoint(string ...$keys): string
    {
        $map = self::getMeta('endpoints', []);

        foreach ($keys as $key) {
            if (!is_array($map) || !array_key_exists($key, $map)) {
                $path = implode('.', $keys);
                throw new \InvalidArgumentException(
                    "Endpoint path not configured in sdk_meta.json for '{$path}'."
                );
            }
            $map = $map[$key];
        }

        if (!is_string($map)) {
            $path = implode('.', $keys);
            throw new \InvalidArgumentException(
                "Endpoint leaf for '{$path}' is not a string."
            );
        }

        return $map;
    }

    private static function decodeKey(string $encoded): string
    {
        $raw = base64_decode($encoded, true);
        if ($raw === false) {
            return '';
        }
        $result = '';
        $keyLen = strlen(self::XOR_KEY);
        for ($i = 0, $len = strlen($raw); $i < $len; $i++) {
            $result .= chr(ord($raw[$i]) ^ ord(self::XOR_KEY[$i % $keyLen]));
        }
        return $result;
    }
}
