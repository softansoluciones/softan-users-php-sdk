<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SoftanUsers\SDK;

/**
 * Validates the structure and content of sdk_meta.json.
 * All checks are offline — no HTTP calls are made.
 */
class SdkMetaTest extends TestCase
{
    private array $meta;

    protected function setUp(): void
    {
        $this->meta = SDK::loadJson(SDK::META_PATH);
    }

    public function test_meta_file_exists(): void
    {
        $this->assertFileExists(SDK::META_PATH);
    }

    public function test_meta_is_valid_json(): void
    {
        $this->assertNotEmpty($this->meta, 'sdk_meta.json must decode to a non-empty array.');
    }

    public function test_meta_has_required_top_level_keys(): void
    {
        foreach (['sdk_name', 'sdk_version', 'app_id', 'default_environment', 'base_urls', 'credentials', 'endpoints'] as $key) {
            $this->assertArrayHasKey($key, $this->meta, "sdk_meta.json is missing key '{$key}'.");
        }
    }

    public function test_app_id_is_correct(): void
    {
        $this->assertSame('SOU-08A', $this->meta['app_id']);
    }

    public function test_default_environment_is_stg(): void
    {
        $this->assertSame('stg', $this->meta['default_environment']);
    }

    public function test_base_urls_has_stg_and_prod(): void
    {
        $this->assertArrayHasKey('stg',  $this->meta['base_urls']);
        $this->assertArrayHasKey('prod', $this->meta['base_urls']);
        $this->assertNotEmpty($this->meta['base_urls']['stg']);
        $this->assertNotEmpty($this->meta['base_urls']['prod']);
    }

    public function test_credentials_has_stg_and_prod(): void
    {
        $this->assertArrayHasKey('stg',  $this->meta['credentials']);
        $this->assertArrayHasKey('prod', $this->meta['credentials']);
        $this->assertArrayHasKey('api_key', $this->meta['credentials']['stg']);
        $this->assertArrayHasKey('api_key', $this->meta['credentials']['prod']);
    }

    public function test_endpoints_has_users_and_usersapps(): void
    {
        $endpoints = $this->meta['endpoints'];
        $this->assertArrayHasKey('users',     $endpoints);
        $this->assertArrayHasKey('usersapps', $endpoints);
        $this->assertArrayHasKey('base', $endpoints['users']);
        $this->assertArrayHasKey('base', $endpoints['usersapps']);
    }

    public function test_endpoints_has_commons(): void
    {
        $commons = $this->meta['endpoints']['commons'] ?? [];
        $this->assertArrayHasKey('countries',            $commons);
        $this->assertArrayHasKey('identification_types', $commons);
    }

    public function test_sdk_version_format(): void
    {
        $this->assertMatchesRegularExpression(
            '/^\d+\.\d+\.\d+$/',
            $this->meta['sdk_version'],
            'sdk_version must follow semver (X.Y.Z).'
        );
    }
}
