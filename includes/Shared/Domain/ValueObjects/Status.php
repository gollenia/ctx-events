<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\ValueObjects;

enum Status: string
{
    case Published = 'publish';
    case Future = 'future';
    case Draft = 'draft';
    case Private = 'private';
    case Trash = 'trash';

    public function isPublic(): bool
    {
        return $this === self::Published;
    }

    public function isDeleted(): bool
    {
        return $this === self::Trash;
    }

    public function isPrivate(): bool
    {
        return $this === self::Private;
    }
}
