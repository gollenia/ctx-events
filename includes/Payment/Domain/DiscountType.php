<?php

namespace Contexis\Events\Payment\Domain;

enum DiscountType: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';
}
