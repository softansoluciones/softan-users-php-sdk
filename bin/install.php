#!/usr/bin/env php
<?php
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
    fwrite(STDERR, "No se encontro el autoloader de Composer.\n");
    exit(1);
}

use SoftanUsers\SDK;

SDK::init();

echo "Softan Users PHP SDK — Configuracion de entorno\n";
echo "Las credenciales estan incluidas en el SDK. Solo selecciona el entorno.\n\n";

$opts    = getopt('', ['env::']);
$default = SDK::$META['default_environment'] ?? 'stg';
$current = SDK::$CONFIG['active_environment'] ?? $default;
$envArg  = $opts['env'] ?? null;

if ($envArg === null) {
    echo "Entorno activo actual: [{$current}]\n";
    echo "Entornos disponibles: stg (staging), prod\n";
    echo "Nuevo entorno (Enter para mantener [{$current}]): ";
    $input  = rtrim(fgets(STDIN));
    $envArg = $input !== '' ? $input : $current;
}

$envArg = strtolower(trim((string) $envArg));
$valid  = array_keys((array) (SDK::$META['base_urls'] ?? []));

if (!in_array($envArg, $valid, true)) {
    fwrite(STDERR, "Entorno invalido: '{$envArg}'. Opciones: " . implode(', ', $valid) . "\n");
    exit(1);
}

$cfg = ['active_environment' => $envArg];

if (SDK::saveJson(SDK::CONFIG_PATH, $cfg)) {
    echo "OK — Entorno activo: {$envArg}\n";
    exit(0);
}

fwrite(STDERR, "ERROR — No se pudo escribir sdk_config.json\n");
exit(1);
