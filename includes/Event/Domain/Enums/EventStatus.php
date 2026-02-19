<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\Enums;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'EventStatus')]
enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'publish';
	case Future = 'future';
    case Pending = 'pending';
    case Private = 'private';
    case Trash = 'trash';
    case Cancelled = 'cancelled';

    public function isPublic(): bool
    {
        return in_array($this, [self::Published]);
    }
}
