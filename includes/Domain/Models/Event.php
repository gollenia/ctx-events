<?php

namespace Contexis\Events\Domain\Models;

use \Contexis\Events\PostTypes\EventPost;
use Contexis\Events\Intl\Price;
use DateTime;
use Contexis\Events\Domain\Collections\BookingCollection;
use Contexis\Events\Domain\Collections\CouponCollection;
use Contexis\Events\Domain\Collections\TicketCollection;
use Contexis\Events\Views\EventView;
use Contexis\Events\PostTypes\RecurringEventPost;
use WP_Post;
use WP_User;
use Contexis\Events\Core\Contracts\Model;
use Contexis\Events\Repositories\BookingRepository;
use Contexis\Events\Core\Utilities\Image;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\EventSchedule;
use Contexis\Events\Domain\ValueObjects\Term;
use Contexis\Events\Domain\ValueObjects\TermCollection;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use Contexis\Events\Intl\Date;
use Contexis\Events\Models\Booking;
use DateTimeImmutable;
use JsonSerializable;
use Mpdf\Tag\B;

final class Event { 

	public function __construct(
		public int $id,
		public string $title,
		public string $description,
		public int $author,
		public EventStatus $status,
		public DateTimeImmutable $created_at,
		public EventSchedule $schedule,
		public BookingPolicy $booking_policy,
		public TicketCollection $tickets,
		public ?int $person_id,
		public ?int $location_id,
	) {}
	
	public function start(): DateTimeImmutable {
		return $this->schedule->start;
	}

	public function end(): DateTimeImmutable {
		return $this->schedule->end;
	}

	function is_public(){
		return $this->status->is_public();
	}

	function get_title(): string {
		return $this->title;
	}

	

}