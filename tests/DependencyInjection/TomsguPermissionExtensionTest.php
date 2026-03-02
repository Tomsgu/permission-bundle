<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Tomsgu\PermissionBundle\DependencyInjection\TomsguPermissionExtension;

class TomsguPermissionExtensionTest extends TestCase
{
    protected ContainerBuilder $configuration;

    protected function tearDown(): void
    {
        unset($this->configuration);
    }

    public function testNotHasDatabase(): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TomsguPermissionExtension();
        $config = $this->getEmptyConfig();
        $loader->load([$config], $this->configuration);
        $this->assertNotHasDefinition('permission_manager_class');
    }

    private function assertNotHasDefinition(string $id): void
    {
        $this->assertFalse(($this->configuration->hasDefinition($id)
            ?: $this->configuration->hasAlias($id)));
    }

    protected function createFullConfiguration(): void
    {
        $this->createConfiguration($this->getFullConfig());
    }

    protected function createEmptyConfiguration(): void
    {
        $this->createConfiguration($this->getEmptyConfig());
    }

    protected function createDatabaseEmptyConfiguration()
    {
        $this->createConfiguration($this->getEmptyDatabaseConfig());
    }

    protected function createConfiguration(array $config): void
    {
        $this->configuration = new ContainerBuilder();
        $loader = new TomsguPermissionExtension();
        $loader->load([$config], $this->configuration);
        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    protected function getEmptyConfig(): array
    {
        $yaml = <<<EOF
permissions:
    - { name: "Test permission", description: "Test permission description" }
    - { name: "Test permission 2" }
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    protected function getEmptyDatabaseConfig(): array
    {
        $yaml = <<<EOF
database: 
    db_driver: orm 
    permission_class: Acme\Entity\Permission
permissions:
    - { name: "Test permission", description: "Test permission description" }
    - { name: "Test permission 2" }
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    protected function getFullConfig(): array
    {
        $yaml = <<<EOF
database: 
    db_driver: orm 
    permission_class: Acme\Entity\Permission
    object_manager_name: not_default
    permission_manager_class: Acme\Entity\PermissionManager
permissions:
    - { name: "Test permission", description: "Test permission description" }
    - { name: "Test permission 2" }
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }
}
