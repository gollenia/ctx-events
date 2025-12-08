# Event Context

## Event

The Event class is the main entity of the event context. It represents an event in the system. It is used to store the event data in the database and to retrieve it from the database.

### Properties

```mermaid
classDiagram
    class Event {
        +EventId id
        -Status status
        +string name
        -DateTimeImmutable startDate
        -DateTimeImmutable endDate
        +DateTimeImmutable createdAt
        +BookingPolicy bookingPolicy
        +EventViewConfig eventViewConfig
        +AuthorId authorId
        +string? description
        +string? audience
        +TicketCollection? tickets
        +LocationId? locationId
        +PersonId? personId
        +ImageId? imageId
        +RecurrenceId? recurrenceId
        +getStatus() Status
        +start() DateTimeImmutable
        +end() DateTimeImmutable
        +duration() DateInterval
        +isOngoing(DateTimeImmutable at) bool
        +isPast(DateTimeImmutable at) bool
        +meetsBookingPolicy() BookingDecision
        +bookingStartsAt() DateTimeImmutable?
        +bookingEndsAt() DateTimeImmutable?
    }
```
