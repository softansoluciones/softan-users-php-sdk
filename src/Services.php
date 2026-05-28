<?php
namespace SoftanUsers;

final class Services
{
    // ----------------------------------------------------------------
    // Users
    // ----------------------------------------------------------------

    /**
     * GET /users — List all users.
     */
    public static function listUsers(?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('users', 'base');
        return Client::request($endpoint, 'GET', $headers, null, null, $verifyTLS);
    }

    /**
     * GET /users/{id} — Show a single user.
     */
    public static function showUser(int $id, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::requirePositive($id, 'id');
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('users', 'base') . '/' . $id;
        return Client::request($endpoint, 'GET', $headers, null, null, $verifyTLS);
    }

    /**
     * POST /users — Create a new user.
     *
     * Required payload fields:
     *   - identification_type  (int)
     *   - user_identification  (string)
     *   - user_name            (string)
     *   - user_last_name       (string)
     *   - user_email           (string)
     *   - country_id           (int)
     *
     * Optional:
     *   - user_phone   (string)
     *   - user_status  (int)
     */
    public static function createUser(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::requireFields($payload, [
            'identification_type', 'user_identification',
            'user_name', 'user_last_name', 'user_email', 'country_id',
        ]);
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('users', 'base');
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    /**
     * PUT /users — Update an existing user.
     *
     * Required payload fields: user_id + all createUser() fields + user_status.
     */
    public static function updateUser(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::requireFields($payload, [
            'user_id', 'identification_type', 'user_identification',
            'user_name', 'user_last_name', 'user_email', 'country_id', 'user_status',
        ]);
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('users', 'base');
        return Client::request($endpoint, 'PUT', $headers, $payload, null, $verifyTLS);
    }

    /**
     * DELETE /users/{id} — Delete a user.
     */
    public static function deleteUser(int $id, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::requirePositive($id, 'id');
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('users', 'base') . '/' . $id;
        return Client::request($endpoint, 'DELETE', $headers, null, null, $verifyTLS);
    }

    // ----------------------------------------------------------------
    // Users Apps
    // ----------------------------------------------------------------

    /**
     * GET /usersapps — List all user-app associations.
     */
    public static function listUserApps(?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('usersapps', 'base');
        return Client::request($endpoint, 'GET', $headers, null, null, $verifyTLS);
    }

    /**
     * GET /usersapps/{id} — Show a single user-app association.
     */
    public static function showUserApp(int $id, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::requirePositive($id, 'id');
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('usersapps', 'base') . '/' . $id;
        return Client::request($endpoint, 'GET', $headers, null, null, $verifyTLS);
    }

    /**
     * POST /usersapps — Create a user-app association.
     *
     * Required payload fields:
     *   - user_id        (int)
     *   - app_identifier (string)
     */
    public static function createUserApp(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::requireFields($payload, ['user_id', 'app_identifier']);
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('usersapps', 'base');
        return Client::request($endpoint, 'POST', $headers, $payload, null, $verifyTLS);
    }

    /**
     * PUT /usersapps — Update a user-app association.
     *
     * Required payload fields:
     *   - user_app_id    (int)
     *   - user_id        (int)
     *   - app_identifier (string)
     *   - user_app_status (int)
     */
    public static function updateUserApp(array $payload, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::requireFields($payload, ['user_app_id', 'user_id', 'app_identifier', 'user_app_status']);
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('usersapps', 'base');
        return Client::request($endpoint, 'PUT', $headers, $payload, null, $verifyTLS);
    }

    /**
     * DELETE /usersapps/{id} — Delete a user-app association.
     */
    public static function deleteUserApp(int $id, ?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        self::requirePositive($id, 'id');
        $headers  = $headers ?: Headers::buildRuntimeHeaders();
        $endpoint = Config::resolveEndpoint('usersapps', 'base') . '/' . $id;
        return Client::request($endpoint, 'DELETE', $headers, null, null, $verifyTLS);
    }

    // ----------------------------------------------------------------
    // Commons
    // ----------------------------------------------------------------

    /**
     * GET /commons/countries — List all available countries.
     */
    public static function listCountries(?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        $headers  = $headers ?: Headers::buildPublicHeaders();
        $endpoint = Config::resolveEndpoint('commons', 'countries');
        return Client::request($endpoint, 'GET', $headers, null, null, $verifyTLS);
    }

    /**
     * GET /commons/identification-types — List all identification types.
     */
    public static function listIdentificationTypes(?array $headers = null, bool $verifyTLS = true): array
    {
        SDK::init();
        $headers  = $headers ?: Headers::buildPublicHeaders();
        $endpoint = Config::resolveEndpoint('commons', 'identification_types');
        return Client::request($endpoint, 'GET', $headers, null, null, $verifyTLS);
    }

    // ----------------------------------------------------------------
    // Internal helpers
    // ----------------------------------------------------------------

    private static function requireFields(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                throw new \InvalidArgumentException("Missing required field: '{$field}'.");
            }
        }
    }

    private static function requirePositive(int $value, string $name): void
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException("'{$name}' must be a positive integer.");
        }
    }
}
