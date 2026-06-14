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
composer require softan/users-php-sdk:^0.2.3
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

### Filtrar usuarios (server-side)

```php
// Filtrado básico
$result = Services::filterUsers([
    'filters' => ['user_status' => 1, 'user_email' => '%@softansol.com'],
    'page'     => 1,
    'per_page' => 20,
    'order_by' => 'created_at',
    'order_dir'=> 'desc',
]);

// $result['data']['users']      → array de usuarios
// $result['data']['pagination'] → { page, per_page, total, total_pages }
```

**Campos filtrables:** `user_id`, `identification_type`, `user_identification`, `user_name`, `user_last_name`, `user_email`, `user_phone`, `country_id`, `user_status`, `created_at` (range), `updated_at` (range)

Modos de filtrado:
- Exacto: `'user_status' => 1`
- LIKE: `'user_email' => '%@softansol.com'`
- IN: `'user_status' => [1, 2]`
- Rango: `'from_created_at' => '2025-01-01', 'to_created_at' => '2025-12-31'`

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

### Filtrar asociaciones usuario-aplicación (server-side)

```php
$result = Services::filterUserApps([
    'filters' => ['app_identifier' => 'SOM-65B', 'user_app_status' => 1],
    'page'     => 1,
    'per_page' => 50,
]);

// $result['data']['usersapps']  → array de asociaciones (incluye user_email, user_name)
// $result['data']['pagination'] → { page, per_page, total, total_pages }
```

**Campos filtrables:** `user_app_id`, `user_id`, `app_identifier`, `user_app_status`, `user_email`, `user_name`, `user_last_name`, `user_status`, `created_at` (range), `updated_at` (range)

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

El entorno activo se resuelve en este orden (mayor a menor prioridad):

| Prioridad | Mecanismo |
|-----------|-----------|
| 1 | `SDK::$CONFIG['active_environment']` — override programático |
| 2 | Variable de entorno `SOFTAN_USERS_ENV` |
| 3 | `sdk_meta.json → default_environment` (valor del paquete: `stg`) |

### Opción A — Variable de entorno (recomendado)

No requiere cambios en código. Se configura una vez a nivel de servidor y sobrevive cualquier `composer install`.

**Apache VirtualHost:**
```apache
SetEnv SOFTAN_USERS_ENV prod
```

**`.htaccess`:**
```apache
SetEnv SOFTAN_USERS_ENV prod
```

**PHP bootstrap / `index.php` (antes de cualquier llamada al SDK):**
```php
putenv('SOFTAN_USERS_ENV=prod');
```

### Opción B — Override programático

Útil cuando el entorno se determina en código (multi-tenant, flags de configuración, etc.). Debe ejecutarse antes de la primera llamada a `Services::*`.

```php
use SoftanUsers\SDK;
use SoftanUsers\Services;

SDK::$CONFIG = ['active_environment' => 'prod'];

$users = Services::listUsers();
```

### Verificar la configuración activa

```bash
php vendor/bin/users-set-env.php
php vendor/bin/users-set-env.php --env=prod   # muestra instrucciones para ese entorno
```

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
