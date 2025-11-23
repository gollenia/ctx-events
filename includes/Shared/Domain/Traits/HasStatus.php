<?php

namespace Contexis\Events\Shared\Domain\Traits;

use Contexis\Events\Shared\Domain\ValueObjects\Status;

trait HasStatus
{
    public function status(): Status
    {
        return $this->status;
    }

    public function isPublished(): bool
    {
        return $this->status === Status::Published;
    }

    public function isDeleted(): bool
    {
        return $this->status === Status::Trash;
    }

    public function isPrivate(): bool
    {
        return $this->status === Status::Private;
    }
}
