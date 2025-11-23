<?php

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Domain\ValueObjects\ViewContext;

final class ViewContextFactory
{
    public static function createFromCurrentUser(): ViewContext
    {
        $user_id = get_current_user_id();
        $can_view = current_user_can('read');
        $can_edit = current_user_can('edit_posts');
        $can_manage_options = current_user_can('manage_options');

        return new ViewContext($user_id, $can_view, $can_edit, $can_manage_options);
    }
}
