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
use SoftanUsers\Config;
use SoftanUsers\Services;

SDK::init();

echo "Softan Users PHP SDK — Instalador\n\n";

function prompt(string $label, bool $hidden = false): string {
    if ($hidden && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        echo $label;
        @system('stty -echo');
        $val = rtrim(fgets(STDIN));
        @system('stty echo');
        echo "\n";
        return $val;
    }
    echo $label;
    return rtrim(fgets(STDIN));
}

$opts      = getopt('', ['api-key::', 'app-id::', 'env::']);
$activeEnv = $opts['env']    ?? (SDK::$CONFIG['active_environment'] ?? (SDK::$META['default_environment'] ?? 'prod'));
$apiKey    = $opts['api-key'] ?? prompt("API Key (X-API-KEY) para [{$activeEnv}]: ", true);
$appId     = $opts['app-id'] ?? prompt("App Identifier (X-App-Id): ");

$cfg = SDK::$CONFIG ?: ['active_environment' => $activeEnv, 'environments' => []];
$cfg['active_environment']       = $activeEnv;
$cfg['environments'][$activeEnv] = ['api_key' => $apiKey, 'app_id' => $appId];

echo "\nValidando credenciales contra {$activeEnv}...\n";
$res = Services::listUsers(null, true);

if (isset($res['success']) && $res['success'] === true) {
    if (SDK::saveJson(SDK::CONFIG_PATH, $cfg)) {
        echo "OK — Configuracion guardada en sdk_config.json\n";
        exit(0);
    }
    fwrite(STDERR, "ERROR — No se pudo escribir sdk_config.json\n");
    exit(1);
}

fwrite(STDERR, "ERROR — No fue posible validar credenciales. Respuesta:\n" . json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
exit(1);
