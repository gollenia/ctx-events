<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

final class ViewContext
{
    public function __construct(
        private readonly int $userId,
        private readonly bool $canView = false,
        private readonly bool $canEdit = false,
        private readonly bool $canManageOptions = false
    ) {
    }

    public function isAnonymous(): bool
    {
        return $this->userId === 0;
    }

    public function canView(): bool
    {
        return $this->canView;
    }

    public function canEdit(): bool
    {
        return $this->canEdit;
    }

    public function isAdmin(): bool
    {
        return $this->canManageOptions;
    }
}
