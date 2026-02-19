<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Traits;

use Contexis\Events\Shared\Domain\ValueObjects\Status;

trait HasStatus
{
    abstract protected function getStatus(): Status;
	abstract public function setStatus(Status $status): static;

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

	public function isDraft(): bool
	{
		return $this->getStatus() === Status::Draft;
	}
}
