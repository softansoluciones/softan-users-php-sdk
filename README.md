# Softan Users SDK (PHP)

[![Latest Stable Version](https://img.shields.io/packagist/v/softan/users-php-sdk.svg)](https://packagist.org/packages/softan/users-php-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/softan/users-php-sdk.svg)](https://packagist.org/packages/softan/users-php-sdk)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

## Descripción

SDK oficial para integrar aplicaciones con Softan Users en PHP. Expone métodos estáticos de alto nivel para gestionar usuarios, asociaciones de aplicación y datos comunes (países, tipos de identificación).

## Requisitos

- PHP 8.1 o superior
- Composer 2
- Extensiones: `ext-curl`, `ext-json`

## Instalación

```bash
composer require softan/users-php-sdk:^0.1.0
```

Luego inicializa la configuración con tu API key y App ID:

```bash
php vendor/bin/install.php
```

### Alternativa: instalación desde GitHub (VCS)

```bash
composer config repositories.softan-users vcs https://github.com/softansoluciones/softan-users-php-sdk
composer require softan/users-php-sdk:dev-main
```

## Quickstart

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use SoftanUsers\Services;

// Listar todos los usuarios
$users = Services::listUsers();

// Crear un usuario
$created = Services::createUser([
    'identification_type' => 1,
    'user_identification' => '123456789',
    'user_name'           => 'Juan',
    'user_last_name'      => 'Pérez',
    'user_email'          => 'juan@example.com',
    'country_id'          => 48,
]);
```

## Uso en código

### Users

```php
use SoftanUsers\Services;

// Listar todos los usuarios
$list = Services::listUsers();

// Ver un usuario por ID
$user = Services::showUser(42);

// Crear usuario
$created = Services::createUser([
    'identification_type' => 1,        // ID del tipo de identificación
    'user_identification' => '987654',  // Número de documento
    'user_name'           => 'María',
    'user_last_name'      => 'García',
    'user_email'          => 'maria@example.com',
    'country_id'          => 48,        // ID del país (Colombia)
    'user_phone'          => '+573001234567',  // Opcional
]);

// Actualizar usuario (requiere user_id + todos los campos + user_status)
$updated = Services::updateUser([
    'user_id'             => 42,
    'identification_type' => 1,
    'user_identification' => '987654',
    'user_name'           => 'María',
    'user_last_name'      => 'García',
    'user_email'          => 'maria@example.com',
    'country_id'          => 48,
    'user_status'         => 1,  // 1=activo, 2=inactivo, 3=sin verificar
]);

// Eliminar usuario
$deleted = Services::deleteUser(42);
```

### Users Apps (asociaciones usuario-aplicación)

```php
// Listar todas las asociaciones
$associations = Services::listUserApps();

// Ver una asociación por ID
$assoc = Services::showUserApp(10);

// Crear asociación
$created = Services::createUserApp([
    'user_id'        => 42,
    'app_identifier' => 'SOM-XXXX',
]);

// Actualizar asociación
$updated = Services::updateUserApp([
    'user_app_id'     => 10,
    'user_id'         => 42,
    'app_identifier'  => 'SOM-XXXX',
    'user_app_status' => 1,  // 1=activo, 2=inactivo, 3=sin verificar
]);

// Eliminar asociación
$deleted = Services::deleteUserApp(10);
```

### Commons

```php
// Listar países disponibles
$countries = Services::listCountries();

// Listar tipos de identificación
$idTypes = Services::listIdentificationTypes();
```

## Autenticación

El SDK usa dos modos de autenticación según el tipo de operación:

| Modo | Headers enviados | Uso |
|------|-----------------|-----|
| **Internal** | `X-API-KEY` + `X-App-Id` | Escritura (create, update, delete) |
| **Public** | Solo `X-API-KEY` | Lectura de datos comunes (countries, id-types) |

Ambos modos se configuran desde `sdk_config.json` — no requieren ningún cambio en el código.

## Configuración

La configuración se gestiona en `sdk_config.json` (creado por `bin/install.php`, **no versionar**).

Estructura:

```json
{
  "active_environment": "prod",
  "environments": {
    "dev":  { "api_key": "", "app_id": "" },
    "stg":  { "api_key": "", "app_id": "" },
    "prod": { "api_key": "", "app_id": "" }
  }
}
```

Copia `sdk_config.json.example` como punto de partida si prefieres configurarlo manualmente.

## CLI

```bash
# Instalador interactivo (crea sdk_config.json)
php vendor/bin/install.php

# Modo no interactivo
php vendor/bin/install.php --api-key="TU_API_KEY" --app-id="SOM-XXXX" --env=prod
```

## TLS

La verificación TLS está habilitada por defecto. Para desarrollo puedes desactivarla por llamada:

```php
Services::listUsers(null, false);       // segundo parámetro: $verifyTLS
Services::createUser($payload, null, false);
```

## Compatibilidad

- PHP: 8.1+
- Sistemas: Windows, Linux, macOS

## Licencia

MIT (ver `composer.json`).
