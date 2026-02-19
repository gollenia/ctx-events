<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Presentation\Factories;

use Contexis\Events\Shared\Domain\ValueObjects\Link;

class WpLink
{
	public static function fromPostId(int $postId): ?Link
	{
		$permalink = get_permalink($postId);
		if ($permalink === false) {
			return null;
		}
		return Link::fromString($permalink);
	}
}