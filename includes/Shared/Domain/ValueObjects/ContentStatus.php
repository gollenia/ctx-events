<?php

namespace Contexis\Events\Shared\Domain;

enum ContentStatus
{
    case Draft;
    case PendingReview;
    case Published;
    case Private;
    case Deleted;

    public function isPublic(): bool
    {
        return $this === self::Published;
    }

    public function isDeleted(): bool
    {
        return $this === self::Deleted;
    }

    public function isPrivate(): bool
    {
        return $this === self::Private;
    }
}
