<?php

use Contexis\Events\Event\Application\Service\RecurringOccurrenceLister;
use Contexis\Events\Event\Application\UseCases\ListOccurrences;
use Contexis\Events\Event\Application\UseCases\DetachRecurringOccurrence;
use Contexis\Events\Event\Application\UseCases\CancelRecurringOccurrence;
use function DI\autowire;

return [
    RecurringOccurrenceLister::class => autowire(),
    ListOccurrences::class => autowire(),
    DetachRecurringOccurrence::class => autowire(),
    CancelRecurringOccurrence::class => autowire(),
];
