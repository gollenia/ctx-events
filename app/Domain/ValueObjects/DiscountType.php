<?php

namespace Contexis\Events\Domain\ValueObjects;

enum DiscountType: string {
    case PERCENT = 'percent';
    case FIXED = 'fixed';
}