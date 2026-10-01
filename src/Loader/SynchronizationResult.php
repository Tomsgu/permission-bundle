<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Loader;

final readonly class SynchronizationResult
{
    /**
     * @param list<string> $created    names added to the storage
     * @param list<string> $updated    names whose description changed
     * @param list<string> $removed    stored names that are no longer declared and were deleted
     * @param list<string> $undeclared stored names that are no longer declared and were kept
     */
    public function __construct(
        public array $created = [],
        public array $updated = [],
        public array $removed = [],
        public array $undeclared = [],
    ) {
    }
}
