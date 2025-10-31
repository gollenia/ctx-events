<?php 

namespace Contexis\Events\Application\DTO;

class BookingSessionDto {

	private string $id;
	private string $event_id;
	private string $user_id;
	private array $attendees;

	public function __construct(string $id, string $event_id, string $user_id, array $attendees) {
		$this->id = $id;
		$this->event_id = $event_id;
		$this->user_id = $user_id;
		$this->attendees = $attendees;
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_event_id(): string {
		return $this->event_id;
	}

	public function get_user_id(): string {
		return $this->user_id;
	}

	public function get_attendees(): array {
		return $this->attendees;
	}
}
