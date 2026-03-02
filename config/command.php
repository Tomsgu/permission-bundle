<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tomsgu\PermissionBundle\Command\LoadPermissionDataCommand;
use Tomsgu\PermissionBundle\Loader\PermissionLoaderInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(LoadPermissionDataCommand::class)
        ->private()
        ->args([
            service(PermissionLoaderInterface::class),
        ])
        ->tag('console.command');
};
