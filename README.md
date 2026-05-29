# Softan Users SDK (PHP)

[![Latest Stable Version](https://img.shields.io/packagist/v/softan/users-php-sdk.svg)](https://packagist.org/packages/softan/users-php-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/softan/users-php-sdk.svg)](https://packagist.org/packages/softan/users-php-sdk)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

## Descripción

SDK oficial para integrar aplicaciones con Softan Users en PHP. Expone métodos estáticos de alto nivel para gestionar usuarios, asociaciones de aplicación y datos comunes (países, tipos de identificación).

Las credenciales de acceso a la API están embebidas en el SDK (`sdk_meta.json`). No se requiere ninguna configuración manual de API keys.

## Requisitos

- PHP 8.1 o superior
- Composer 2
- Extensiones: `ext-curl`, `ext-json`

## Instalación

```bash
composer require softan/users-php-sdk:^0.2.0
```

Listo. No se necesita ningún paso adicional — las credenciales están embebidas.

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

// Listar todos los usuarios (entorno stg por defecto)
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
| **Internal** | `X-API-KEY` + `X-App-Id` | Escritura y lectura de recursos (users, usersapps) |
| **Public** | Solo `X-API-KEY` | Lectura de datos comunes (countries, id-types) |

Las credenciales están embebidas en el SDK y se seleccionan automáticamente según el entorno activo.

## Entornos

El SDK incluye credenciales para `stg` y `prod`. El entorno por defecto es `stg`.

### Cambiar el entorno activo

**Opción 1 — CLI (recomendado para configuración persistente):**

```bash
# Interactivo
php vendor/bin/users-set-env.php

# No interactivo
php vendor/bin/users-set-env.php --env=prod
php vendor/bin/users-set-env.php --env=stg
```

Crea o actualiza `sdk_config.json` con el entorno seleccionado. Ese archivo persiste entre requests hasta que se vuelva a ejecutar el script.

**Opción 2 — Programática (recomendado para proyectos con entorno fijo en código):**

El SDK usa inicialización lazy: si el proyecto pre-configura `SDK::$META` y `SDK::$CONFIG` antes de la primera llamada a un servicio, esos valores se usan durante todo el ciclo de vida de la request.

```php
use SoftanUsers\SDK;
use SoftanUsers\Services;

// Forzar entorno prod (llamar antes del primer Services::*)
SDK::$META   = SDK::loadJson(SDK::META_PATH);
SDK::$CONFIG = ['active_environment' => 'prod'];

// Todas las llamadas siguientes usarán prod
$users = Services::listUsers();
```

Lo habitual es encapsular esto en un método de inicialización del servicio que consume el SDK:

```php
private function initUsersSdk(): void
{
    \SoftanUsers\SDK::$META   = \SoftanUsers\SDK::loadJson(\SoftanUsers\SDK::META_PATH);
    \SoftanUsers\SDK::$CONFIG = ['active_environment' => 'prod'];
}
```

Si no se realiza ninguna inicialización previa, el SDK usa `stg` como entorno por defecto (definido en `sdk_meta.json`).

## Configuración

Las credenciales de API están embebidas en `sdk_meta.json` (XOR+base64). No es necesario ni recomendable crear un `sdk_config.json` con credenciales.

El único uso válido de `sdk_config.json` es sobrescribir el entorno activo cuando se prefiere configuración en archivo en lugar de código:

```json
{
  "active_environment": "prod"
}
```

`sdk_config.json` debe ir en `.gitignore` si se crea. No está incluido en el repositorio del SDK.

## TLS

La verificación TLS está habilitada por defecto. Para desarrollo puedes desactivarla por llamada:

```php
Services::listUsers(null, false);        // segundo parámetro: $verifyTLS
Services::createUser($payload, null, false);
```

## Compatibilidad

- PHP: 8.1+
- Sistemas: Windows, Linux, macOS

## Desarrollo

```bash
composer install
composer test
```

CI: el workflow en `.github/workflows/ci.yml` valida Composer e integra PHPUnit en PHP 8.1/8.2/8.3.

## Licencia

MIT (ver `composer.json`).
