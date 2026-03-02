<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tomsgu\PermissionBundle\Entity\Permission;

class PermissionTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $permission = new class('ROLE_ADMIN', 'Admin permission') extends Permission {
            public function getId(): mixed
            {
                return 1;
            }
        };

        $this->assertSame('ROLE_ADMIN', $permission->getName());
        $this->assertSame('Admin permission', $permission->getDescription());
    }
}
