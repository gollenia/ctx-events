# Architecture

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