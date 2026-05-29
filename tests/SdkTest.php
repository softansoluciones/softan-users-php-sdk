<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SoftanUsers\SDK;

/**
 * Tests SDK::loadJson() and SDK::saveJson() in isolation.
 * No HTTP calls — filesystem only, using temp files.
 */
class SdkTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = sys_get_temp_dir() . '/softan_users_sdk_test_' . uniqid() . '.json';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function test_load_json_returns_empty_array_for_nonexistent_file(): void
    {
        $result = SDK::loadJson('/nonexistent/path/to/file.json');
        $this->assertSame([], $result);
    }

    public function test_load_json_parses_valid_json_object(): void
    {
        file_put_contents($this->tmpFile, '{"foo":"bar","num":42}');
        $result = SDK::loadJson($this->tmpFile);
        $this->assertSame(['foo' => 'bar', 'num' => 42], $result);
    }

    public function test_load_json_parses_valid_json_array(): void
    {
        file_put_contents($this->tmpFile, '[1,2,3]');
        // JSON arrays decode to indexed PHP arrays — valid
        $result = SDK::loadJson($this->tmpFile);
        $this->assertIsArray($result);
    }

    public function test_load_json_returns_empty_for_invalid_json(): void
    {
        file_put_contents($this->tmpFile, 'not valid json {{');
        $result = SDK::loadJson($this->tmpFile);
        $this->assertSame([], $result);
    }

    public function test_load_json_returns_empty_for_empty_file(): void
    {
        file_put_contents($this->tmpFile, '');
        $result = SDK::loadJson($this->tmpFile);
        $this->assertSame([], $result);
    }

    public function test_save_and_load_roundtrip(): void
    {
        $data = ['sdk_version' => '0.2.0', 'app_id' => 'SOU-08A', 'nested' => ['a' => 1]];
        SDK::saveJson($this->tmpFile, $data);
        $result = SDK::loadJson($this->tmpFile);
        $this->assertSame($data, $result);
    }

    public function test_meta_path_constant_resolves_to_existing_file(): void
    {
        $this->assertFileExists(SDK::META_PATH);
    }
}
