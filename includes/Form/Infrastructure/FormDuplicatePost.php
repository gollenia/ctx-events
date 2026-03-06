<?php
declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Wordpress\DuplicatePost;

class FormDuplicatePost extends DuplicatePost
{
	protected function supportsPostType(string $postType): bool
	{
		return FormPostTypes::isFormPostType($postType);
	}
}
