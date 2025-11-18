<?php

namespace Contexis\Events\Shared\Domain\Traits;

use Contexis\Events\Shared\Domain\ContentStatus;

trait HasContentStatus
{
    public function status(): ContentStatus
    {
        return $this->status;
    }

    public function isPublished(): bool
    {
        return $this->status === ContentStatus::Published;
    }

    public function isDeleted(): bool
    {
        return $this->status === ContentStatus::Deleted;
    }

    public function isPrivate(): bool
    {
        return $this->status === ContentStatus::Private;
    }
}
