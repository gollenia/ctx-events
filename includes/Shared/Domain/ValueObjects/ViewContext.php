<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

final class ViewContext
{
    public function __construct(
        private readonly int $userId, // 0 for anonymous
        private readonly bool $canEditPosts = false, // author
        private readonly bool $canEditOthersPosts = false,  // editor
        private readonly bool $canManageOptions = false // admin
    ) {
    }

    public function isAnonymous(): bool
    {
        return $this->userId === 0;
    }

    public function canEditOwnPosts(): bool
    {
        return $this->canEditPosts && !$this->canEditOthersPosts;
    }

    public function canEditOthersPosts(): bool
    {
        return $this->canEditOthersPosts;
    }

    public function isAdmin(): bool
    {
        return $this->canManageOptions;
    }
}
