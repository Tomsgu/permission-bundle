<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Loader;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tomsgu\PermissionBundle\Loader\PermissionLoader;
use Tomsgu\PermissionBundle\Model\PermissionInterface;
use Tomsgu\PermissionBundle\Model\PermissionManagerInterface;

class PermissionLoaderTest extends TestCase
{
    private PermissionManagerInterface&MockObject $permissionManager;

    protected function setUp(): void
    {
        $this->permissionManager = $this->createMock(PermissionManagerInterface::class);
    }

    public function testLoadPermissionsCreatesNewPermissions(): void
    {
        $permissions = [
            ['name' => 'ROLE_ADMIN', 'description' => 'Admin access'],
            ['name' => 'ROLE_USER', 'description' => 'User access'],
        ];

        $this->permissionManager->method('findPermissionBy')->willReturn(null);

        $perm1 = $this->createStub(PermissionInterface::class);
        $perm2 = $this->createStub(PermissionInterface::class);

        $this->permissionManager->expects($this->exactly(2))
            ->method('createPermission')
            ->willReturnCallback(fn (string $name, string $description) => match ($name) {
                'ROLE_ADMIN' => $perm1,
                'ROLE_USER' => $perm2,
            });

        // 2 calls with andFlush=false + 1 final call with andFlush=true (default)
        $this->permissionManager->expects($this->exactly(3))
            ->method('updatePermission');

        $loader = new PermissionLoader($this->permissionManager, $permissions);
        $loader->loadPermissions();
    }

    public function testLoadPermissionsSkipsExisting(): void
    {
        $permissions = [
            ['name' => 'ROLE_ADMIN', 'description' => 'Admin access'],
        ];

        $existing = $this->createStub(PermissionInterface::class);
        $this->permissionManager->expects($this->once())
            ->method('findPermissionBy')
            ->with(['name' => 'ROLE_ADMIN'])
            ->willReturn($existing);

        $this->permissionManager->expects($this->never())->method('createPermission');
        $this->permissionManager->expects($this->never())->method('updatePermission');

        $loader = new PermissionLoader($this->permissionManager, $permissions);
        $loader->loadPermissions();
    }

    public function testLoadPermissionsEmptyConfig(): void
    {
        $this->permissionManager->expects($this->never())->method('findPermissionBy');
        $this->permissionManager->expects($this->never())->method('createPermission');
        $this->permissionManager->expects($this->never())->method('updatePermission');

        $loader = new PermissionLoader($this->permissionManager, []);
        $loader->loadPermissions();
    }

    public function testLoadPermissionsFlushesOnce(): void
    {
        $permissions = [
            ['name' => 'ROLE_ADMIN', 'description' => 'Admin'],
            ['name' => 'ROLE_USER', 'description' => 'User'],
        ];

        $this->permissionManager->method('findPermissionBy')->willReturn(null);

        $perm = $this->createStub(PermissionInterface::class);
        $this->permissionManager->method('createPermission')->willReturn($perm);

        $calls = [];
        $this->permissionManager->expects($this->exactly(3))
            ->method('updatePermission')
            ->willReturnCallback(function (PermissionInterface $p, ?bool $flush = true) use (&$calls) {
                $calls[] = $flush;
            });

        $loader = new PermissionLoader($this->permissionManager, $permissions);
        $loader->loadPermissions();

        // First two calls are with andFlush=false, last call is with andFlush=true (default)
        $this->assertSame(false, $calls[0]);
        $this->assertSame(false, $calls[1]);
        $this->assertTrue($calls[2] === null || $calls[2] === true);
    }
}
