<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Attendee;

final class AttendeeCollection extends AbstractTypedCollection {

	public function __construct(Attendee ...$attendees)
	{
		$this->items = $attendees;
	}
}