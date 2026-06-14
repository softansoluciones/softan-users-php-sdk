#!/usr/bin/env php
<?php
/**
 * Softan Users SDK — Environment switcher
 *
 * Switches the active environment by writing sdk_config.json next to sdk_meta.json.
 *
 * Usage:
 *   php vendor/bin/users-set-env.php             (interactive)
 *   php vendor/bin/users-set-env.php --env=prod
 *   php vendor/bin/users-set-env.php --env=stg
 */

declare(strict_types=1);

$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
    getcwd() . '/vendor/autoload.php',
];
$loaded = false;
foreach ($autoloadCandidates as $candidate) {
    if (is_file($candidate)) { require $candidate; $loaded = true; break; }
}
if (!$loaded || !class_exists('SoftanUsers\\SDK')) {
    fwrite(STDERR, "No se encontró el autoloader de Composer.\n");
    exit(1);
}

use SoftanUsers\SDK;

SDK::init();

$configPath = SDK::CONFIG_PATH;
$validEnvs  = array_keys((array) (SDK::$META['base_urls'] ?? []));
$current    = SDK::$CONFIG['active_environment'] ?? SDK::$META['default_environment'] ?? 'stg';

// Parse --env flag
$env = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--env=')) {
        $env = strtolower(trim(substr($arg, 6)));
    }
}

// Interactive prompt if no flag provided
if ($env === null) {
    echo "\n  Softan Users SDK — Cambiar entorno\n";
    echo "  ===================================\n";
    echo "  Entorno actual  : {$current}\n";
    echo "  Disponibles     : " . implode(', ', $validEnvs) . "\n\n";
    echo "  Nuevo entorno (Enter para mantener [{$current}]): ";
    $input = strtolower(trim((string) fgets(STDIN)));
    $env   = $input !== '' ? $input : $current;
}

// Validate
if (!in_array($env, $validEnvs, true)) {
    fwrite(STDERR, "  Error: '{$env}' no es válido. Opciones: " . implode(', ', $validEnvs) . "\n");
    exit(1);
}

// Write sdk_config.json at the project root
if (!SDK::saveJson($configPath, ['active_environment' => $env])) {
    fwrite(STDERR, "  Error: no se pudo escribir en {$configPath}\n");
    exit(1);
}

echo "  OK — Entorno activo: {$env}\n";
echo "  Config en: {$configPath}\n\n";
