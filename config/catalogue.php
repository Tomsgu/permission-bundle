<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tomsgu\PermissionBundle\Catalogue\PermissionCatalogue;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(PermissionCatalogue::class)
        ->private()
        ->args([
            param('tomsgu_permission.permissions'),
            param('tomsgu_permission.categories'),
            param('tomsgu_permission.levels'),
        ]);
};
