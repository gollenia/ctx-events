<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Traits;

use Contexis\Events\Shared\Domain\ValueObjects\Status;

trait HasStatus
{
    abstract protected function getStatus(): Status;

    public function status(): Status
    {
        return $this->getStatus();
    }

    public function isPublished(): bool
    {
        return $this->getStatus() === Status::Published;
    }

    public function isDeleted(): bool
    {
        return $this->getStatus() === Status::Trash;
    }

    public function isPrivate(): bool
    {
        return $this->getStatus() === Status::Private;
    }
}
