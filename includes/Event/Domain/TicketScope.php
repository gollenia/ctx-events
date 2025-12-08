<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain;

enum TicketScope
{
    case ALL;            // Alle Tickets (auch Drafts, Abgelaufene)
    case BOOKABLE_ONLY;  // Nur Enabled + CurrentlyAvailable
    case NONE;           // Keine Tickets
}
