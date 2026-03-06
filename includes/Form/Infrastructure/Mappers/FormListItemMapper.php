<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure\Mappers;

use Contexis\Events\Form\Application\DTOs\FormListItem;
use Contexis\Events\Form\Domain\AttendeeForm;
use Contexis\Events\Form\Domain\BookingForm;
use Contexis\Events\Form\Domain\Fields\FormField;
use Contexis\Events\Form\Domain\Form;
use Contexis\Events\Form\Domain\Fields\FormFieldCollection;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Form\Domain\Enums\FieldWidth;
use Contexis\Events\Form\Infrastructure\Contracts\DetailsMapper;
use Contexis\Events\Form\Infrastructure\FormPostTypes;
use Contexis\Events\Platform\Wordpress\PluginInfo;
use Contexis\Events\Shared\Application\ValueObjects\TaxonomyCollection;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;
use Contexis\Events\Shared\Infrastructure\Contracts\PostMapper;
use Contexis\Events\Shared\Infrastructure\Wordpress\BlockAttributesResolver;


final class FormListItemMapper
{
    public static function map(PostSnapshot $snapshot, array $usageCounts): FormListItem
    {
       
		$type = FormPostTypes::fromPostType($snapshot->post_type);
		
			
        return new FormListItem(
			id: FormId::from($snapshot->ID),	
			type: $type,
            title: $snapshot->post_title,
            description: $snapshot->post_excerpt,
			createdAt: new \DateTimeImmutable($snapshot->post_date),
			usageCount: $usageCounts[$snapshot->ID] ?? 0,
			tags: new TaxonomyCollection(...wp_get_post_tags($snapshot->ID, ['fields' => 'all'])),
			status: Status::from($snapshot->post_status),
        );
	}

}