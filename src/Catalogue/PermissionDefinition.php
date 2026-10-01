<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Catalogue;

final readonly class PermissionDefinition
{
    public function __construct(
        public string $name,
        public ?string $label = null,
        public string $description = '',
        public ?string $category = null,
        public ?string $level = null,
    ) {
    }

    /**
     * The label to show to people, falling back to the technical name.
     */
    public function displayLabel(): string
    {
        return $this->label ?? $this->name;
    }
}
