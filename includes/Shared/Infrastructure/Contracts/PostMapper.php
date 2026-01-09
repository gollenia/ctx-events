<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Contracts;

use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

interface PostMapper
{
	public static function map(PostSnapshot $post): object;
}