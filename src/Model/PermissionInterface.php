<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Model;

interface PermissionInterface
{
    public function getId(): mixed;

    public function getName(): string;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;
}
