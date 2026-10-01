<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tomsgu\PermissionBundle\Catalogue\PermissionCatalogue;
use Tomsgu\PermissionBundle\Loader\PermissionSynchronizer;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(PermissionSynchronizer::class)
        ->private()
        ->args([
            service('tomsgu_permission.permission_manager'),
            service(PermissionCatalogue::class),
        ]);
};
