<?php

namespace Contexis\Events\Application\DTO;

class BookingSessionDto
{
    private string $id;
    private string $event_id;
    private string $user_id;
    private array $attendees;

    public function __construct(string $id, string $event_id, string $user_id, array $attendees)
    {
        $this->id = $id;
        $this->event_id = $event_id;
        $this->user_id = $user_id;
        $this->attendees = $attendees;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEventId(): string
    {
        return $this->event_id;
    }

    public function getUserId(): string
    {
        return $this->user_id;
    }

    public function getAttendees(): array
    {
        return $this->attendees;
    }
}
