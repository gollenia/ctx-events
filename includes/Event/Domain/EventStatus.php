<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'publish';
    case Pending = 'pending';
    case Private = 'private';
    case Trash = 'trash';
    case Cancelled = 'cancelled';

    public function isPublic(): bool
    {
        return in_array($this, [self::Published]);
    }
}
