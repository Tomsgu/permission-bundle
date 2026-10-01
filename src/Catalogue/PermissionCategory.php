<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Catalogue;

final readonly class PermissionCategory
{
    public function __construct(
        public string $key,
        public string $label,
        public ?string $description = null,
    ) {
    }
}
