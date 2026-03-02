<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TomsguPermissionBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
