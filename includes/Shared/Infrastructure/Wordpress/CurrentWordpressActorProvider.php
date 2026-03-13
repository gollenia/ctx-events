<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Domain\ValueObjects\Actor;

final class CurrentWordpressActorProvider implements CurrentActorProvider
{
    public function current(): Actor
    {
        $currentUser = wp_get_current_user();
        $displayName = $currentUser->display_name ?: $currentUser->user_nicename ?: $currentUser->user_login ?: '';

        return new Actor(
            id: (int) $currentUser->ID,
            name: $displayName,
        );
    }
}
