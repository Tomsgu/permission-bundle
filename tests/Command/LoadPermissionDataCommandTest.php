<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tomsgu\PermissionBundle\Catalogue\PermissionCatalogue;
use Tomsgu\PermissionBundle\Command\LoadPermissionDataCommand;
use Tomsgu\PermissionBundle\Loader\PermissionSynchronizer;
use Tomsgu\PermissionBundle\Tests\Loader\InMemoryPermissionManager;

class LoadPermissionDataCommandTest extends TestCase
{
    public function testUndeclaredPermissionsAreReportedAndKept(): void
    {
        $manager = new InMemoryPermissionManager();
        $manager->store('OBSOLETE', '');
        $tester = new CommandTester($this->command($manager));

        $exitCode = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Created (1): POST_EDIT', $tester->getDisplay());
        $this->assertStringContainsString('No longer declared (1), run again with --prune to delete them: OBSOLETE', $tester->getDisplay());
        $this->assertArrayHasKey('OBSOLETE', $manager->stored);
    }

    public function testPruneDeletesUndeclaredPermissions(): void
    {
        $manager = new InMemoryPermissionManager();
        $manager->store('OBSOLETE', '');
        $tester = new CommandTester($this->command($manager));

        $tester->execute(['--prune' => true]);

        $this->assertStringContainsString('Removed (1): OBSOLETE', $tester->getDisplay());
        $this->assertArrayNotHasKey('OBSOLETE', $manager->stored);
    }

    public function testCommandName(): void
    {
        $this->assertSame('tomsgu:permission:load', $this->command(new InMemoryPermissionManager())->getName());
    }

    private function command(InMemoryPermissionManager $manager): LoadPermissionDataCommand
    {
        return new LoadPermissionDataCommand(new PermissionSynchronizer(
            $manager,
            new PermissionCatalogue([['name' => 'POST_EDIT', 'description' => 'Can edit posts.']])
        ));
    }
}
