<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tomsgu\PermissionBundle\Command\LoadPermissionDataCommand;
use Tomsgu\PermissionBundle\Loader\PermissionLoaderInterface;

class LoadPermissionDataCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $loader = $this->createMock(PermissionLoaderInterface::class);
        $loader->expects($this->once())->method('loadPermissions');

        $command = new LoadPermissionDataCommand($loader);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Permissions were successfully loaded.', $tester->getDisplay());
    }

    public function testCommandName(): void
    {
        $loader = $this->createStub(PermissionLoaderInterface::class);
        $command = new LoadPermissionDataCommand($loader);

        $this->assertSame('tomsgu:permission:load', $command->getName());
    }
}
