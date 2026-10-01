<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

class TomsguPermissionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $config = $processor->processConfiguration($configuration, $configs);

        if (!empty($config['database'])) {
            /** @var array{db_driver: string, permission_class: string, object_manager_name: string|null, permission_manager_class: string} $databaseConfig */
            $databaseConfig = $config['database'];
            $this->loadDoctrine($databaseConfig, $container, $loader);
        }

        if (!empty($config['cache'])) {
            /** @var array{cache_prefix: string, expires_at: int} $cacheConfig */
            $cacheConfig = $config['cache'];
            $this->loadCachedUserManager($cacheConfig, $container, $loader);
        } else {
            $this->loadBasicUserManager($container, $loader);
        }

        /** @var array<string, array<string, mixed>> $permissions */
        $permissions = $config['permissions'];
        $container->setParameter('tomsgu_permission.permissions', $this->inDeclarationOrder($permissions, $configs));
        $container->setParameter('tomsgu_permission.categories', is_array($config['categories']) ? $config['categories'] : []);
        $container->setParameter('tomsgu_permission.levels', is_array($config['levels']) ? $config['levels'] : []);
        $loader->load('catalogue.php');
        if (!empty($config['database'])) {
            $loader->load('synchronizer.php');
            $loader->load('command.php');
        }
    }

    /**
     * Orders permissions by their last declaration, so a configuration that declares a permission
     * again also decides where it is listed.
     *
     * @param array<string, array<string, mixed>> $permissions
     * @param array<array-key, mixed> $configs
     *
     * @return array<string, array<string, mixed>>
     */
    private function inDeclarationOrder(array $permissions, array $configs): array
    {
        $order = [];
        foreach ($configs as $config) {
            $declared = is_array($config) ? ($config['permissions'] ?? []) : [];
            if (!is_array($declared)) {
                continue;
            }
            foreach ($declared as $key => $permission) {
                $name = is_array($permission) && is_string($permission['name'] ?? null) ? $permission['name'] : (string) $key;
                unset($order[$name]);
                $order[$name] = true;
            }
        }

        $ordered = [];
        foreach (array_keys($order) as $name) {
            if (isset($permissions[$name])) {
                $ordered[$name] = $permissions[$name];
            }
        }

        return $ordered + $permissions;
    }

    private function loadBasicUserManager(ContainerBuilder $container, Loader\PhpFileLoader $loader): void
    {
        $loader->load('user_manager.php');
        $container->setAlias(
            'Tomsgu\PermissionBundle\Model\UserManagerInterface',
            new Alias('tomsgu_permission.manager.user', true)
        );
    }

    /**
     * @param array{cache_prefix: string, expires_at: int} $config
     */
    private function loadCachedUserManager(array $config, ContainerBuilder $container, Loader\PhpFileLoader $loader): void
    {
        // Sets a correct cache adapter.
        $container->setAlias('tomsgu_permission.cache_adapter', new Alias('cache.app', false));
        // Sets a cache prefix.
        $container->setParameter('tomsgu_permission.cache.prefix', $config['cache_prefix']);
        // Sets cache expire parameter.
        $container->setParameter('tomsgu_permission.cache.expire_at', $config['expires_at']);

        $loader->load('cached_user_manager.php');
        $container->setAlias(
            'Tomsgu\PermissionBundle\Model\UserManagerInterface',
            new Alias('tomsgu_permission.manager.cached_user', true)
        );
    }

    /**
     * @param array{db_driver: string, permission_class: string, object_manager_name: string|null, permission_manager_class: string} $config
     */
    private function loadDoctrine(array $config, ContainerBuilder $container, Loader\PhpFileLoader $loader): void
    {
        // Sets permission class parameter.
        $container->setParameter('tomsgu_permission.model.permission_class', $config['permission_class']);

        // Sets object manager name.
        if ($config['object_manager_name'] === null) {
            $container->setParameter('tomsgu_permission.model.manager_name', 'default');
        } else {
            $container->setParameter('tomsgu_permission.model.manager_name', $config['object_manager_name']);
        }

        // Sets permission manager class parameter.
        $container->setParameter('tomsgu_permission.permission_manager_class', $config['permission_manager_class']);

        // Configure doctrine manager.
        $loader->load('doctrine.php');
        $container->setAlias('tomsgu_permission.doctrine_registry', new Alias('doctrine', false));
        $definition = $container->getDefinition('tomsgu_permission.object_manager');
        $definition->setFactory([new Reference('tomsgu_permission.doctrine_registry'), 'getManager']);

        // Set a PermissionManagerInterface alias.
        $container->setAlias(
            'tomsgu_permission.permission_manager',
            new Alias($config['permission_manager_class'], false)
        );
        $container->setAlias(
            'Tomsgu\PermissionBundle\Model\PermissionManagerInterface',
            new Alias('tomsgu_permission.permission_manager', false)
        );

        // Set a loader permission.
        $loader->load('loader.php');
        $container->setAlias(
            'Tomsgu\PermissionBundle\Loader\PermissionLoaderInterface',
            new Alias('tomsgu_permission.loader.permission', false)
        );
    }
}
