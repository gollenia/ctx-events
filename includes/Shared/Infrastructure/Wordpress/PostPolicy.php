<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

abstract class PostPolicy
{
    public static function canView(int $postId): bool
    {
        if (is_user_logged_in()) {
            return current_user_can('read_post', $postId);
        }

        return is_post_publicly_viewable($postId);
    }

    public static function canEdit(int $postId): bool
    {
        return current_user_can('edit_post', $postId);
    }
}
