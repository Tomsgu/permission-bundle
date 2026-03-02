<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Loader;

interface PermissionLoaderInterface
{
    /**
     * Loads a permissions from the configuration file.
     */
    public function loadPermissions(): void;
}
