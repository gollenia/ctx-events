<?php

namespace Contexis\Events\Domain\ValueObjects;


enum EventStatus: string {
	case Draft = 'draft';
	case Published = 'publish';
	case Pending = 'pending';
	case Private = 'private';
	case Trash = 'trash';
	case Cancelled = 'cancelled';

	public function is_public(): bool {
		return in_array($this, [self::Published]);
	}
}