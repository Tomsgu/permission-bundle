<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Tomsgu\PermissionBundle\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    private function processConfig(array $configs): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }

    public function testDefaultValues(): void
    {
        $config = $this->processConfig([[]]);

        $this->assertSame([], $config['permissions']);
    }

    public function testPermissionsConfig(): void
    {
        $config = $this->processConfig([
            [
                'permissions' => [
                    ['name' => 'ROLE_ADMIN', 'description' => 'Admin'],
                    ['name' => 'ROLE_USER', 'description' => 'User'],
                ],
            ],
        ]);

        $this->assertCount(2, $config['permissions']);
        $this->assertSame('ROLE_ADMIN', $config['permissions']['ROLE_ADMIN']['name']);
        $this->assertSame('Admin', $config['permissions']['ROLE_ADMIN']['description']);
    }

    public function testDatabaseConfig(): void
    {
        $config = $this->processConfig([
            [
                'database' => [
                    'db_driver' => 'orm',
                    'permission_class' => 'App\\Entity\\Permission',
                ],
            ],
        ]);

        $this->assertSame('orm', $config['database']['db_driver']);
        $this->assertSame('App\\Entity\\Permission', $config['database']['permission_class']);
        $this->assertNull($config['database']['object_manager_name']);
        $this->assertSame('tomsgu_permission.permission_manager.default', $config['database']['permission_manager_class']);
    }

    public function testCacheConfig(): void
    {
        $config = $this->processConfig([
            [
                'cache' => [
                    'cache_prefix' => 'custom.prefix.',
                    'expires_at' => 7200,
                ],
            ],
        ]);

        $this->assertSame('custom.prefix.', $config['cache']['cache_prefix']);
        $this->assertSame(7200, $config['cache']['expires_at']);
    }

    public function testCacheDefaultValues(): void
    {
        $config = $this->processConfig([
            [
                'cache' => [],
            ],
        ]);

        $this->assertSame('tomsgu_permission.user.permissions.', $config['cache']['cache_prefix']);
        $this->assertSame(86400, $config['cache']['expires_at']);
    }
}
