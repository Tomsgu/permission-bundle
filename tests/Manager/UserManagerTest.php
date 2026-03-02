<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Manager;

use PHPUnit\Framework\TestCase;
use Tomsgu\PermissionBundle\Manager\UserManager;
use Tomsgu\PermissionBundle\Model\PermissionInterface;
use Tomsgu\PermissionBundle\Model\UserPermissionInterface;

class UserManagerTest extends TestCase
{
    private UserManager $userManager;

    protected function setUp(): void
    {
        $this->userManager = new UserManager();
    }

    public function testGetPermissions(): void
    {
        $perm1 = $this->createStub(PermissionInterface::class);
        $perm1->method('getName')->willReturn('ROLE_ADMIN');

        $perm2 = $this->createStub(PermissionInterface::class);
        $perm2->method('getName')->willReturn('ROLE_USER');

        $user = $this->createStub(UserPermissionInterface::class);
        $user->method('getPermissions')->willReturn([$perm1, $perm2]);

        $this->assertSame(['ROLE_ADMIN', 'ROLE_USER'], $this->userManager->getPermissions($user));
    }

    public function testGetPermissionsEmpty(): void
    {
        $user = $this->createStub(UserPermissionInterface::class);
        $user->method('getPermissions')->willReturn([]);

        $this->assertSame([], $this->userManager->getPermissions($user));
    }

    public function testHasPermission(): void
    {
        $perm = $this->createStub(PermissionInterface::class);
        $perm->method('getName')->willReturn('ROLE_ADMIN');

        $user = $this->createStub(UserPermissionInterface::class);
        $user->method('getPermissions')->willReturn([$perm]);

        $this->assertTrue($this->userManager->hasPermission($user, 'ROLE_ADMIN'));
    }

    public function testHasPermissionReturnsFalse(): void
    {
        $user = $this->createStub(UserPermissionInterface::class);
        $user->method('getPermissions')->willReturn([]);

        $this->assertFalse($this->userManager->hasPermission($user, 'ROLE_ADMIN'));
    }

    public function testHasPermissions(): void
    {
        $perm1 = $this->createStub(PermissionInterface::class);
        $perm1->method('getName')->willReturn('ROLE_ADMIN');

        $perm2 = $this->createStub(PermissionInterface::class);
        $perm2->method('getName')->willReturn('ROLE_USER');

        $user = $this->createStub(UserPermissionInterface::class);
        $user->method('getPermissions')->willReturn([$perm1, $perm2]);

        $this->assertTrue($this->userManager->hasPermissions($user, ['ROLE_ADMIN', 'ROLE_USER']));
    }

    public function testHasPermissionsReturnsFalseWhenOneMissing(): void
    {
        $perm = $this->createStub(PermissionInterface::class);
        $perm->method('getName')->willReturn('ROLE_USER');

        $user = $this->createStub(UserPermissionInterface::class);
        $user->method('getPermissions')->willReturn([$perm]);

        $this->assertFalse($this->userManager->hasPermissions($user, ['ROLE_ADMIN', 'ROLE_USER']));
    }

    public function testInvalidatePermissionsIsNoop(): void
    {
        $user = $this->createStub(UserPermissionInterface::class);

        $this->userManager->invalidatePermissions($user);

        $this->assertTrue(true);
    }
}
