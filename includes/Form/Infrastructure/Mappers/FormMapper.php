<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure\Mappers;

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
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;
use Contexis\Events\Shared\Infrastructure\Contracts\PostMapper;
use Contexis\Events\Shared\Infrastructure\Wordpress\BlockAttributesResolver;


class FormMapper implements PostMapper
{
    public static function map(PostSnapshot $snapshot): Form
    {
        $fields = self::getFormFields($snapshot->post_content);
		$type = FormPostTypes::fromPostType($snapshot->post_type);
		
			
        $args = [
            'id' => FormId::from($snapshot->ID),
			'type' => $type,
            'name' => $snapshot->post_title,
            'description' => $snapshot->post_excerpt,
            'fields' => $fields,
        ];

		return match($type) {
            FormType::BOOKING  => new BookingForm(...$args),
            FormType::ATTENDEE => new AttendeeForm(...$args),
        };
           
    }

	public static function getFormFields(string $content): FormFieldCollection {

		$blocks = self::getFieldBlocks($content);

		if(empty($blocks)) return FormFieldCollection::fromArray([]);

		$fields = [];
		
		foreach( $blocks as $key => $block ) {
			$type = self::getFieldType($block['blockName']);
			
			$defaults = BlockAttributesResolver::getDefaults($block['blockName']);
            $attributes = array_merge($defaults, $block['attrs'] ?? []);

			if($type == 'html') {
				$attributes['rendered_content'] = render_block($block);
				$attributes['name'] = $attributes['name'] ?? 'html_' . uniqid();
				
			}

			if ($type === 'email') {
                $attributes['inputType'] = 'email'; // String für Enum::from()
            }
            if ($type === 'phone') {
                $attributes['inputType'] = 'tel';
            }

			$mapper = self::getDetailsMapper($type);
			if (!$mapper) continue;
			
			$rawRule = $attributes['visibilityRule'] ?? null;
			$visibilityRule = is_array($rawRule) && isset($rawRule['field'])
				? new \Contexis\Events\Form\Domain\ValueObjects\VisibilityRule(
					dependsOnField: (string) $rawRule['field'],
					expectedValue: $rawRule['value'] ?? null,
					operator: (string) ($rawRule['operator'] ?? 'equals'),
				)
				: null;

			$fields[] = new FormField(
				name: $attributes['name'],
				label: $attributes['label'],
				required: $attributes['required'] ?? false,
				width: isset($attributes['width'])
					? FieldWidth::from((int)$attributes['width'])
					: FieldWidth::SIX,
				description: $attributes['description'] ?? '',
				details: $mapper->map($attributes),
				visibilityRule: $visibilityRule,
			);
			
		}

		return FormFieldCollection::fromArray($fields);
	}


	private static function getDetailsMapper(string $type): ?DetailsMapper
    {
        return match($type) {
            'text', 'email', 'phone', 'url', 'password' => new InputMapper(),
            'textarea' => new TextareaMapper(),
            'checkbox' => new CheckboxMapper(),
            'select'   => new SelectMapper(),
            'html'     => new HtmlMapper(),
            default    => null
        };
    }


	public static function getFieldBlocks(string $content): array {
		
		$blocks = parse_blocks( $content );
		
		if(count($blocks) == 0) return [];
		if(!array_key_exists('innerBlocks', $blocks[0])) return [];

		return $blocks[0]['innerBlocks'];
	}

	public static function getFieldType(string $blockname): string {
		return substr($blockname, strripos($blockname, '-') + 1);
	}

	public static function getBlockDefaults(string $type): array {
		$defaults = [];
		$file = PluginInfo::getPluginDir() . "/src/blocks/form/" . $type . "/block.json";
		if(!$file) return $defaults;
		$block_data = json_decode( file_get_contents($file) );
		foreach($block_data->attributes as $key => $value) {
			if(!property_exists($value, 'default')) continue;
			$defaults[$key] = $value->default;
		}
		
		return $defaults;
	}
}