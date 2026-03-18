<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain\Enums;

enum EmailTarget: string
{
	case CUSTOMER = 'customer';
	case ADMIN = 'admin';
	case BILLING_CONTACT = 'billing_contact';
	case EVENT_CONTACT = 'event_contact';
}