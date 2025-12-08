<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Application\ValueObjects\UserContext;

final class UserContextFactory
{
    public static function createFromCurrentUser(): UserContext
    {
        $userId = get_current_user_id();
        $canView = current_user_can('read');
        $canEdit = current_user_can('edit_posts');
        $canManageOptions = current_user_can('manage_options');

        return new UserContext($userId, $canView, $canEdit, $canManageOptions);
    }
}
