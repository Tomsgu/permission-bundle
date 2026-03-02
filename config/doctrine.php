<?php

declare(strict_types=1);

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tomsgu\PermissionBundle\Manager\Doctrine\PermissionManager;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('tomsgu_permission.permission_manager.default', PermissionManager::class)
        ->private()
        ->args([
            service('tomsgu_permission.object_manager'),
            param('tomsgu_permission.model.permission_class'),
        ]);

    $services->set('tomsgu_permission.object_manager', ObjectManager::class)
        ->private()
        ->args([
            param('tomsgu_permission.model.manager_name'),
        ]);
};
