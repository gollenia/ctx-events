<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Abstracts;

use Contexis\Events\Shared\Infrastructure\Contracts\HasHooks;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;
use Contexis\Events\Shared\Infrastructure\Contracts\HasPatterns;
use Contexis\Events\Shared\Infrastructure\Contracts\HasTaxonomies;

abstract class PostType
{
	public const POST_TYPE = '';

	public function register(): void
	{
		$this->registerPostType();

		if ($this instanceof HasTaxonomies) {
			$this->registerTaxonomies();
		}

		if ($this instanceof HasMetaData) {
			$this->registerMeta();
		}

		if ($this instanceof HasPatterns) {
			$this->registerPatterns();
		}
		
		if ($this instanceof HasHooks) {
			$this->registerHooks();
		}
	}

	abstract public function registerPostType(): void;
}
