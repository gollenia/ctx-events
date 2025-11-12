<?php

namespace Contexis\Events\Presentation\Factories;

use Contexis\Events\Application\Security\ViewContext;

final class ViewContextFactory
{
    public static function createFromCurrentUser(): ViewContext
    {
        $user_id = get_current_user_id();
        $can_edit_posts = current_user_can('edit_posts');
        $can_edit_others_posts = current_user_can('edit_others_posts');
        $can_manage_options = current_user_can('manage_options');

        return new ViewContext($user_id, $can_edit_posts, $can_edit_others_posts, $can_manage_options);
    }

	public function fromRequest(\WP_REST_Request $request): ViewContext {
		$user_id = get_current_user_id();
		$can_edit_posts = current_user_can('edit_posts');
		$can_edit_others_posts = current_user_can('edit_others_posts');
		$can_manage_options = current_user_can('manage_options');

		return new ViewContext($user_id, $can_edit_posts, $can_edit_others_posts, $can_manage_options);
	}
}
