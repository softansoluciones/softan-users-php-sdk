#!/usr/bin/env php
<?php
/**
 * Softan Users SDK — Environment info & setup guide
 *
 * The active environment is resolved in this order:
 *   1. SDK::$CONFIG['active_environment']  (programmatic override)
 *   2. SOFTAN_USERS_ENV env var            (server / process environment)
 *   3. sdk_meta.json → default_environment (package default: stg)
 *
 * This script shows the current resolution and instructions for each method.
 *
 * Usage:
 *   php vendor/bin/users-set-env.php
 *   php vendor/bin/users-set-env.php --env=prod   (writes an Apache/env suggestion)
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
use SoftanUsers\Config;

SDK::init();

$programmatic = SDK::$CONFIG['active_environment'] ?? null;
$envVar       = getenv('SOFTAN_USERS_ENV') ?: null;
$default      = SDK::$META['default_environment'] ?? 'stg';
$active       = Config::getActiveEnvironment();

// Target env from --env flag
$target = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--env=')) {
        $target = strtolower(trim(substr($arg, 6)));
    }
}

$validEnvs = array_keys((array) (SDK::$META['base_urls'] ?? []));

echo "\n";
echo "  Softan Users SDK — Environment Setup\n";
echo "  ======================================\n\n";
echo "  Resolution (highest → lowest priority):\n";
echo "  ┌─────────────────────────────────────────────────────────────┐\n";
printf("  │  1. SDK::\$CONFIG['active_environment']  %-18s│\n", $programmatic ? "→ \"{$programmatic}\"" : '(not set)');
printf("  │  2. SOFTAN_USERS_ENV (env var)          %-18s│\n", $envVar        ? "→ \"{$envVar}\""        : '(not set)');
printf("  │  3. sdk_meta.json default               → \"%-14s\" │\n", $default);
echo "  └─────────────────────────────────────────────────────────────┘\n";
echo "  Active environment: {$active}\n\n";

$target = $target ?? $active;

if (!in_array($target, $validEnvs, true)) {
    fwrite(STDERR, "  Entorno inválido: '{$target}'. Opciones: " . implode(', ', $validEnvs) . "\n\n");
    exit(1);
}

echo "  ── Cómo configurar el entorno \"{$target}\" ──\n\n";

echo "  Opción A — Variable de entorno en Apache VirtualHost (recomendado):\n";
echo "    SetEnv SOFTAN_USERS_ENV {$target}\n\n";

echo "  Opción B — Variable de entorno en .htaccess:\n";
echo "    SetEnv SOFTAN_USERS_ENV {$target}\n\n";

echo "  Opción C — PHP (bootstrap / index.php, antes de cualquier llamada al SDK):\n";
echo "    putenv('SOFTAN_USERS_ENV={$target}');\n\n";

echo "  Opción D — Override programático (máxima prioridad):\n";
echo "    \\SoftanUsers\\SDK::\$CONFIG = ['active_environment' => '{$target}'];\n\n";
