<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation;

trait BookingControllerHelpers
{
    public function checkBookingAdminPermission(): bool
    {
        return current_user_can('manage_options');
    }

    public function isValidBookingUuid(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9-]{6,64}$/', $value) === 1;
    }

    private function getBaseArgs(): array
    {
        return [
            'uuid' => [
                'required'          => true,
                'type'              => 'string',
                'description'       => 'unique booking identifier',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => [$this, 'isValidBookingUuid'],
            ],
        ];
    }
}
