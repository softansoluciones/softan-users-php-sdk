#!/usr/bin/env php
<?php
/**
 * Softan Users SDK — Environment selector
 *
 * Sets the active environment (stg or prod) in sdk_config.json.
 * Credentials are embedded in the SDK — this script only controls
 * which environment is used for API calls.
 *
 * Usage:
 *   php vendor/bin/users-set-env.php
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

$configPath = SDK::configPath();
$validEnvs  = array_keys((array) (SDK::$META['base_urls'] ?? ['stg' => '', 'prod' => '']));

// Parse --env flag
$env = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--env=')) {
        $env = trim(substr($arg, 6));
    }
}

echo "\n  Softan Users SDK — Environment Setup\n";
echo "  =====================================\n\n";

// Interactive prompt if not passed as flag
if ($env === null) {
    $current = SDK::$CONFIG['active_environment'] ?? SDK::$META['default_environment'] ?? 'stg';

    echo "  Current environment : {$current}\n";
    echo "  Available           : " . implode(', ', $validEnvs) . "\n\n";
    echo "  Enter environment (default: {$current}): ";
    $input = trim((string) fgets(STDIN));
    $env   = $input !== '' ? $input : $current;
}

// Validate
$env = strtolower($env);
if (!in_array($env, $validEnvs, true)) {
    fwrite(STDERR, "  Error: '{$env}' is not a valid environment. Options: " . implode(', ', $validEnvs) . "\n\n");
    exit(1);
}

// Write sdk_config.json at the project root
$config = ['active_environment' => $env];
$result = SDK::saveJson($configPath, $config);

if (!$result) {
    fwrite(STDERR, "  Error: could not write sdk_config.json at {$configPath}\n\n");
    exit(1);
}

echo "\n  Active environment set to : {$env}\n";
echo "  Config saved to           : {$configPath}\n\n";
