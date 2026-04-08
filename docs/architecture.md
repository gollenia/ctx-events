# Architecture

See also:

- `docs/icons.md` for the shared icon registry and cross-plugin icon registration model

## Domain

```mermaid
classDiagram
    Event --> Ticket
    Ticket --> Booking
    Booking --> Payment
    Payment --> Invoice
```

## Application

```mermaid
classDiagram
    Event --> Ticket
    Ticket --> Booking
    Booking --> Payment
    Payment --> Invoice
```
