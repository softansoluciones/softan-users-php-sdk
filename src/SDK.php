<?php
namespace SoftanUsers;

final class SDK
{
    public const META_PATH   = __DIR__ . '/../sdk_meta.json';
    public const CONFIG_PATH = __DIR__ . '/../sdk_config.json';

    public static array $META   = [];
    public static array $CONFIG = [];

    /**
     * Lazy initializer — only loads from disk the first time.
     *
     * Environment resolution order (highest to lowest priority):
     *   1. SDK::$CONFIG['active_environment']  (programmatic override)
     *   2. sdk_config.json → active_environment (written by users-set-env.php)
     *   3. sdk_meta.json   → default_environment (package default: 'stg')
     */
    public static function init(): void
    {
        if (self::$META !== []) {
            return;
        }
        self::$META = self::loadJson(self::META_PATH);

        if (self::$CONFIG === []) {
            self::$CONFIG = self::loadJson(self::CONFIG_PATH);
        }
    }

    public static function loadJson(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }
        $json = file_get_contents($path);
        $data = json_decode((string) $json, true);
        return is_array($data) ? $data : [];
    }

    public static function saveJson(string $path, array $data): bool
    {
        return (bool) file_put_contents(
            $path,
            json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
