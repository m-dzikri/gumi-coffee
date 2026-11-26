<?php

namespace Tests\Feature;

use Tests\TestCase;

class VercelConfigTest extends TestCase
{
    public function test_ac9_vercel_json_and_api_index_configure_serverless_runtime_paths(): void
    {
        $vercelJsonPath = base_path('vercel.json');
        $apiIndexPath = base_path('api/index.php');

        $this->assertFileExists($vercelJsonPath);
        $this->assertFileExists($apiIndexPath);

        $vercelConfig = json_decode((string) file_get_contents($vercelJsonPath), true);
        $this->assertIsArray($vercelConfig, 'vercel.json must contain valid JSON.');

        $env = $vercelConfig['env'] ?? [];
        $this->assertIsArray($env);

        $this->assertStringStartsWith('/tmp', (string) ($env['VIEW_COMPILED_PATH'] ?? ''));
        $this->assertStringStartsWith('/tmp', (string) ($env['APP_CONFIG_CACHE'] ?? ''));
        $this->assertStringStartsWith('/tmp', (string) ($env['APP_ROUTES_CACHE'] ?? ''));
        $this->assertSame('stderr', $env['LOG_CHANNEL'] ?? null);
        $this->assertSame('cookie', $env['SESSION_DRIVER'] ?? null);

        $apiIndexContent = (string) file_get_contents($apiIndexPath);
        $this->assertStringContainsString('/tmp/views', $apiIndexContent);
        $this->assertStringContainsString('/tmp/cache', $apiIndexContent);
        $this->assertStringContainsString('public/index.php', $apiIndexContent);
    }
}
