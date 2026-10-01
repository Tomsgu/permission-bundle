<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Loader;

use PHPUnit\Framework\TestCase;
use Tomsgu\PermissionBundle\Catalogue\PermissionCatalogue;
use Tomsgu\PermissionBundle\Loader\PermissionSynchronizer;

class PermissionSynchronizerTest extends TestCase
{
    public function testMissingPermissionsAreCreatedAndDescriptionsRefreshed(): void
    {
        $manager = new InMemoryPermissionManager();
        $manager->store('POST_EDIT', 'Old description.');
        $manager->store('POST_SHOW', 'Can see posts.');

        $result = $this->synchronizer($manager)->synchronize();

        self::assertSame(['POST_DELETE'], $result->created);
        self::assertSame(['POST_EDIT'], $result->updated);
        self::assertSame([], $result->removed);
        self::assertSame('Can edit posts.', $manager->stored['POST_EDIT']->getDescription());
        self::assertSame('Can delete posts.', $manager->stored['POST_DELETE']->getDescription());
        self::assertSame(1, $manager->flushes);
    }

    public function testUndeclaredPermissionsAreOnlyReportedByDefault(): void
    {
        $manager = new InMemoryPermissionManager();
        $manager->store('OBSOLETE', '');

        $result = $this->synchronizer($manager)->synchronize();

        self::assertSame(['OBSOLETE'], $result->undeclared);
        self::assertSame([], $result->removed);
        self::assertArrayHasKey('OBSOLETE', $manager->stored);
    }

    public function testPruningDeletesUndeclaredPermissions(): void
    {
        $manager = new InMemoryPermissionManager();
        $manager->store('OBSOLETE', '');
        $manager->store('POST_SHOW', 'Can see posts.');

        $result = $this->synchronizer($manager)->synchronize(true);

        self::assertSame(['OBSOLETE'], $result->removed);
        self::assertSame([], $result->undeclared);
        self::assertArrayNotHasKey('OBSOLETE', $manager->stored);
        self::assertArrayHasKey('POST_SHOW', $manager->stored);
    }

    public function testNothingIsWrittenWhenEverythingMatches(): void
    {
        $manager = new InMemoryPermissionManager();
        $manager->store('POST_SHOW', 'Can see posts.');
        $manager->store('POST_EDIT', 'Can edit posts.');
        $manager->store('POST_DELETE', 'Can delete posts.');

        $result = $this->synchronizer($manager)->synchronize(true);

        self::assertSame([[], [], [], []], [$result->created, $result->updated, $result->removed, $result->undeclared]);
        self::assertSame(0, $manager->flushes);
    }

    private function synchronizer(InMemoryPermissionManager $manager): PermissionSynchronizer
    {
        return new PermissionSynchronizer($manager, new PermissionCatalogue([
            ['name' => 'POST_SHOW', 'description' => 'Can see posts.'],
            ['name' => 'POST_EDIT', 'description' => 'Can edit posts.'],
            ['name' => 'POST_DELETE', 'description' => 'Can delete posts.'],
        ]));
    }
}
