<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tomsgu\PermissionBundle\Manager\CachedUserManager;
use Tomsgu\PermissionBundle\Utils\CacheHelper;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('tomsgu_permission.helper.cache', CacheHelper::class)
        ->private()
        ->args([
            service('tomsgu_permission.cache_adapter'),
            param('tomsgu_permission.cache.expire_at'),
        ]);

    $services->set('tomsgu_permission.manager.cached_user', CachedUserManager::class)
        ->public()
        ->args([
            service('tomsgu_permission.helper.cache'),
            param('tomsgu_permission.cache.prefix'),
        ]);
};
