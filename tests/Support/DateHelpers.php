<?php

declare(strict_types=1);

function toImmutable(DateTimeInterface $date): DateTimeImmutable {
    return $date instanceof DateTimeImmutable
        ? $date
        : DateTimeImmutable::createFromMutable($date);
}