<?php
namespace SoftanUsers;

final class SDK
{
    public const META_PATH = __DIR__ . '/../sdk_meta.json';

    public static array $META   = [];
    public static array $CONFIG = [];

    /**
     * Resolve the path to sdk_config.json at the consuming project's root.
     *
     * Walks up the directory tree from src/ until it finds the directory that
     * contains vendor/autoload.php — that is the project root. This ensures the
     * config survives composer install/update (which wipes vendor/).
     *
     * Falls back to the package directory (development / standalone use).
     */
    public static function configPath(): string
    {
        $dir = __DIR__;
        for ($i = 0; $i < 8; $i++) {
            if (is_file($dir . '/vendor/autoload.php')) {
                return $dir . '/sdk_config.json';
            }
            $dir = dirname($dir);
        }
        return __DIR__ . '/../sdk_config.json';
    }

    /**
     * Lazy initializer — only loads from disk the first time.
     * If the consuming project pre-sets $META and $CONFIG before the first
     * service call, those values are preserved for the entire request lifecycle.
     *
     * To force a specific environment programmatically:
     *   SDK::$META   = SDK::loadJson(SDK::META_PATH);
     *   SDK::$CONFIG = ['active_environment' => 'prod'];
     */
    public static function init(): void
    {
        if (self::$META !== []) {
            return;
        }
        self::$META   = self::loadJson(self::META_PATH);
        self::$CONFIG = self::loadJson(self::configPath());
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
