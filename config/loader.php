<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tomsgu\PermissionBundle\Loader\PermissionLoader;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('tomsgu_permission.loader.permission', PermissionLoader::class)
        ->private()
        ->args([
            service('tomsgu_permission.permission_manager'),
            param('tomsgu_permission.permissions'),
        ]);
};
