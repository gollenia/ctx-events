<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Domain\ValueObjects;

use Contexis\Events\Form\Domain\FormId;

final class EventForms
{
    public function __construct(
        public readonly ?FormId $bookingForm,
        public readonly ?FormId $attendeeForm,
    ) {
    }

	public function hasBookingForm(): bool
	{
		return $this->bookingForm !== null;
	}

	public function hasAttendeeForm(): bool
	{
		return $this->attendeeForm !== null;
	}

	public function hasForms(): bool
	{
		return $this->hasBookingForm() || $this->hasAttendeeForm();
	}
}