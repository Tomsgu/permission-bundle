<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Manager;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tomsgu\PermissionBundle\Manager\CachedUserManager;
use Tomsgu\PermissionBundle\Model\PermissionInterface;
use Tomsgu\PermissionBundle\Model\UserPermissionInterface;
use Tomsgu\PermissionBundle\Utils\CacheHelperInterface;

class CachedUserManagerTest extends TestCase
{
    private CacheHelperInterface&MockObject $cacheHelper;
    private CachedUserManager $manager;

    protected function setUp(): void
    {
        $this->cacheHelper = $this->createMock(CacheHelperInterface::class);
        $this->manager = new CachedUserManager($this->cacheHelper, 'prefix.');
    }

    private function createUserStub(int $id, array $permissions = []): UserPermissionInterface
    {
        $user = $this->createStub(UserPermissionInterface::class);
        $user->method('getId')->willReturn($id);
        $user->method('getPermissions')->willReturn($permissions);

        return $user;
    }

    public function testGetPermissionsFromCache(): void
    {
        $user = $this->createUserStub(42);
        $cached = ['ROLE_ADMIN', 'ROLE_USER'];

        $this->cacheHelper->expects($this->once())
            ->method('get')
            ->with('prefix.42')
            ->willReturn($cached);

        $this->cacheHelper->expects($this->never())->method('save');

        $this->assertSame($cached, $this->manager->getPermissions($user));
    }

    public function testGetPermissionsCacheMiss(): void
    {
        $perm = $this->createStub(PermissionInterface::class);
        $perm->method('getName')->willReturn('ROLE_ADMIN');

        $user = $this->createUserStub(42, [$perm]);

        $this->cacheHelper->expects($this->once())
            ->method('get')
            ->with('prefix.42')
            ->willReturn(null);

        $this->cacheHelper->expects($this->once())
            ->method('save')
            ->with('prefix.42', ['ROLE_ADMIN']);

        $this->assertSame(['ROLE_ADMIN'], $this->manager->getPermissions($user));
    }

    public function testGetPermissionsCacheMissEmptyPermissions(): void
    {
        $user = $this->createUserStub(42);

        $this->cacheHelper->expects($this->once())
            ->method('get')
            ->with('prefix.42')
            ->willReturn(null);

        $this->cacheHelper->expects($this->never())->method('save');

        $this->assertSame([], $this->manager->getPermissions($user));
    }

    public function testInvalidatePermissions(): void
    {
        $user = $this->createUserStub(42);

        $this->cacheHelper->expects($this->once())
            ->method('delete')
            ->with('prefix.42');

        $this->manager->invalidatePermissions($user);
    }
}
