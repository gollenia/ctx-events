<?php
declare(strict_types=1);

namespace Contexis\Events\Payment\Domain;

enum DiscountType: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';
}
