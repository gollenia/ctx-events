<?php

declare(strict_types = 1);

namespace Contexis\Events\Payment\Domain\Enums;

enum TransactionStatus: int {
	case PENDING = 0;   
    case PAID = 1;      
    case FAILED = 2;    
	case EXPIRED = 3;
    case REFUNDED = 4;  
    case CANCELED = 5; 
}
 