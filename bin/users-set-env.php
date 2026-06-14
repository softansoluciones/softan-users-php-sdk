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
foreach ($autoloadCandidates as $candidate) {
    if (is_file($candidate)) { require $candidate; break; }
}

use SoftanUsers\SDK;

SDK::init();

$configPath = SDK::CONFIG_PATH;
$validEnvs  = ['stg', 'prod'];

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
    $current = 'stg';
    if (is_file($configPath)) {
        $existing = json_decode((string) file_get_contents($configPath), true);
        $current  = (string) ($existing['active_environment'] ?? 'stg');
    }

    echo "  Current environment: {$current}\n";
    echo "  Available environments: stg, prod\n\n";
    echo "  Enter environment [stg/prod] (default: {$current}): ";
    $input = trim((string) fgets(STDIN));
    $env   = $input !== '' ? $input : $current;
}

// Validate
$env = strtolower($env);
if (!in_array($env, $validEnvs, true)) {
    echo "  Error: '{$env}' is not a valid environment. Use 'stg' or 'prod'.\n\n";
    exit(1);
}

// Write sdk_config.json
$config = ['active_environment' => $env];
$result = file_put_contents(
    $configPath,
    json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
);

if ($result === false) {
    echo "  Error: could not write sdk_config.json at {$configPath}\n\n";
    exit(1);
}

echo "\n  Active environment set to: {$env}\n";
echo "  Config saved to: {$configPath}\n\n";
