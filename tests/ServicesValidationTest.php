<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SoftanUsers\SDK;
use SoftanUsers\Services;

/**
 * Tests that Services methods throw InvalidArgumentException for invalid input
 * BEFORE any HTTP call is attempted.
 *
 * SDK::init() loads the local sdk_meta.json (present in repo) and an empty
 * sdk_config.json (not committed — defaults to stg). All assertions happen
 * during the validation phase; no network access occurs.
 */
class ServicesValidationTest extends TestCase
{
    protected function setUp(): void
    {
        SDK::$META   = SDK::loadJson(SDK::META_PATH);
        SDK::$CONFIG = [];
    }

    // ------------------------------------------------------------------
    // Users — ID validation
    // ------------------------------------------------------------------

    public function test_show_user_rejects_zero_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::showUser(0);
    }

    public function test_show_user_rejects_negative_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::showUser(-5);
    }

    public function test_delete_user_rejects_zero_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::deleteUser(0);
    }

    // ------------------------------------------------------------------
    // Users — required field validation
    // ------------------------------------------------------------------

    public function test_create_user_rejects_empty_payload(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::createUser([]);
    }

    public function test_create_user_rejects_missing_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::createUser([
            'identification_type' => 1,
            'user_identification' => '12345678',
            'user_name'           => 'John',
            'user_last_name'      => 'Doe',
            // user_email missing
            'country_id'          => 1,
        ]);
    }

    public function test_create_user_rejects_empty_string_field(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::createUser([
            'identification_type' => 1,
            'user_identification' => '',  // empty string treated as missing
            'user_name'           => 'John',
            'user_last_name'      => 'Doe',
            'user_email'          => 'john@example.com',
            'country_id'          => 1,
        ]);
    }

    public function test_update_user_rejects_missing_user_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::updateUser([
            'identification_type' => 1,
            'user_identification' => '12345678',
            'user_name'           => 'John',
            'user_last_name'      => 'Doe',
            'user_email'          => 'john@example.com',
            'country_id'          => 1,
            'user_status'         => 1,
            // user_id missing
        ]);
    }

    // ------------------------------------------------------------------
    // User Apps — validation
    // ------------------------------------------------------------------

    public function test_show_user_app_rejects_zero_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::showUserApp(0);
    }

    public function test_create_user_app_rejects_missing_app_identifier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::createUserApp(['user_id' => 1]); // app_identifier missing
    }

    public function test_create_user_app_rejects_missing_user_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::createUserApp(['app_identifier' => 'SOM-65B']); // user_id missing
    }

    public function test_update_user_app_rejects_empty_payload(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::updateUserApp([]);
    }

    public function test_delete_user_app_rejects_zero_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Services::deleteUserApp(0);
    }
}
