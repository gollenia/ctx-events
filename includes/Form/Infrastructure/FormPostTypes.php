<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure;

use Contexis\Events\Form\Domain\Enums\FormType;

class FormPostTypes
{
    public static function getPostType(FormType $formType): string
    {
        return match ($formType) {
            FormType::BOOKING => BookingFormPost::POST_TYPE,
            FormType::ATTENDEE => AttendeeFormPost::POST_TYPE,
        };
    }

	public static function isFormPostType(string $postType): bool
	{
		return $postType === BookingFormPost::POST_TYPE || $postType === AttendeeFormPost::POST_TYPE;
	}

	public static function fromPostType(string $postType): FormType
	{
		return match ($postType) {
			BookingFormPost::POST_TYPE => FormType::BOOKING,
			AttendeeFormPost::POST_TYPE => FormType::ATTENDEE,
			default => throw new \InvalidArgumentException("Invalid form post type: " . $postType)
		};
	}
}