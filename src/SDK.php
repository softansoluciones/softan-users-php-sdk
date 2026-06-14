<?php
namespace SoftanUsers;

final class SDK
{
    public const META_PATH = __DIR__ . '/../sdk_meta.json';

    public static array $META   = [];

    /**
     * Programmatic environment override — set before the first service call.
     * Takes priority over sdk_config.json and sdk_meta.json default.
     *
     * Example:
     *   SDK::$CONFIG = ['active_environment' => 'prod'];
     */
    public static array $CONFIG = [];

    /**
     * Resolve the path to sdk_config.json at the consuming project's root.
     *
     * Walks up the directory tree from src/ until it finds the directory
     * containing vendor/autoload.php — that is the project root. This ensures
     * sdk_config.json survives composer install/update (which wipes vendor/).
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
        // Fallback for standalone / development use
        return __DIR__ . '/../sdk_config.json';
    }

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

        // Only load config from disk if no programmatic override was set
        if (self::$CONFIG === []) {
            self::$CONFIG = self::loadJson(self::configPath());
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
