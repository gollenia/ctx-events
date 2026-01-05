<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure;

use Contexis\Events\Form\Domain\FormCollection;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\FormRepository;
use Contexis\Events\Form\Domain\ValueObjects\FormType;
use Contexis\Events\Form\Domain\Form;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

class WpFormRepository implements FormRepository
{
    public function find(FormId $formId): ?Form
    {
        $post = get_post($formId->toInt());
        if (!$post) {
            return null;
        }
		$snapshot = new PostSnapshot($post)
        
            
    }

	public function findByType(FormType $formType): ?FormCollection
	{
		return null;
	}
}
