<?php

namespace Tests\Support;

use DateTimeImmutable;
use DateTimeInterface;

final class DateHelpers
{
    public static function toImmutable(DateTimeInterface $date): DateTimeImmutable
    {
        return $date instanceof DateTimeImmutable
            ? $date
            : DateTimeImmutable::createFromMutable($date);
    }
}
