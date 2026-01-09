<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Domain\Enums;

enum TicketScope
{
    case ALL;            
    case BOOKABLE_ONLY;  
    case NONE;           
}
