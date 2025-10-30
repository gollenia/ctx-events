<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\Collections\TicketCollection;
use Contexis\Events\Repositories\BookingRepository;

class EventSpaces implements \JsonSerializable {
	
	private ?int $event_id = null;
	private ?int $booked = null;
	private ?int $pending = null;
	private ?int $available = null;
	private ?int $canceled = null;
	private ?int $rejected = null; // This is not used in the current implementation, but can be set if needed.
	private ?int $capacity = null;
	public ?int $awaiting_payment = null; // This is not used in the current implementation, but can be set if needed.
	
	public static function from_event(Event $event) : EventSpaces {
		return new self($event->id);
	}

	public function __construct($event_id) {
		$this->event_id = $event_id;
	}

	 public function booked(): int {
        $this->load();
        return $this->booked;
    }

    public function pending(): int {
        $this->load();
        return $this->pending;
    }

    public function available(): int {
        $this->load();
        return $this->available;
    }

	public function canceled(): int {
		$this->load();
		return $this->canceled;
	}

	public function rejected(): int {
		$this->load();
		return $this->rejected; // Default to 0 if not set
	}

	public function capacity(): int {
		$this->load();
		return $this->capacity;
	}

	private function get_capacity() : int {
		$tickets = TicketCollection::find_by_event_id($this->event_id);
		if( empty($tickets) ) return 0; //no tickets, no spaces
		$spaces = 0;
		foreach($tickets as $ticket){
			$spaces += $ticket->ticket_spaces;
		}
		$this->capacity = $spaces;
		return $this->capacity;
	}

	private function load(): void {
        if (!is_null($this->booked)) return;
        $results = BookingRepository::sum_event_spaces($this->event_id);
		$this->capacity = $this->get_capacity();
        $this->booked = (int) $results['booked'] ?? 0;
        $this->pending = (int) $results['pending'] ?? 0;
		$this->canceled = (int) $results['canceled'] ?? 0;
		$this->rejected = (int) $results['rejected'] ?? 0;
        $this->available = max(0, $this->capacity - $this->booked - $this->pending);
    }
	
	public function to_array(): array {
		$this->load();
		return [
			'event_id' => $this->event_id,
			'booked' => $this->booked,
			'pending' => $this->pending,
			'available' => $this->available,
			'capacity' => $this->capacity,
			'awaiting_payment' => $this->awaiting_payment
		];
	}

	public function booked_percent(): float {
		$this->load();
		if ($this->capacity <= 0) return 0.0;
		return round(($this->booked / $this->capacity) * 100, 2);
	}

	public function pending_percent(): float {
		$this->load();
		if ($this->capacity <= 0) return 0.0;
		return round(($this->pending / $this->capacity) * 100, 2);
	}

	public function available_percent(): float {
		$this->load();
		if ($this->capacity <= 0) return 0.0;
		return round(($this->available / $this->capacity) * 100, 2);
	}

	public function jsonSerialize(): array {
		$this->load();
		return $this->to_array();
	}
}