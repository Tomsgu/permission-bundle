<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Entity;

use Tomsgu\PermissionBundle\Model\PermissionInterface;

abstract class Permission implements PermissionInterface
{
    public function __construct(protected string $name, protected ?string $description)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
