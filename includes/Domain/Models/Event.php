<?php

namespace Contexis\Events\Domain\Models;

use Contexis\Events\PostTypes\EventPost;
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
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Domain\ValueObjects\BookingPolicy;
use Contexis\Events\Domain\ValueObjects\Id\EventId;
use Contexis\Events\Domain\ValueObjects\EventSchedule;
use Contexis\Events\Domain\ValueObjects\Term;
use Contexis\Events\Domain\ValueObjects\TermCollection;
use Contexis\Events\Domain\ValueObjects\EventStatus;
use Contexis\Events\Domain\ValueObjects\Id\AuthorId;
use Contexis\Events\Domain\ValueObjects\Id\LocationId;
use Contexis\Events\Domain\ValueObjects\Id\RecurrenceId;
use Contexis\Events\Intl\Date;
use Contexis\Events\Models\Booking;
use DateTimeImmutable;
use JsonSerializable;
use Mpdf\Tag\B;

final class Event
{
    public function __construct(
        public readonly EventId $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $audience,
        public readonly EventStatus $eventStatus,
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate,
        public readonly DateTimeImmutable $createdAt,
        public readonly BookingPolicy $booking_policy,
        public readonly AuthorId $author_id,
        public readonly ?LocationId $location_id,
        public readonly ?ImageId $attachment_id,
        public readonly ?RecurrenceId $recurrence_id
    ) {
    }

    public function start(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function end(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function duration(): \DateInterval
    {
        return $this->startDate->diff($this->endDate);
    }

    public function isPublic()
    {
        return $this->eventStatus->isPublic();
    }
}
