<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain\ValueObjects;

final readonly class EventMailSettings
{
    public function __construct(
        public bool $sendAdminMailsToResponsible = false,
        public bool $responsibleAsReplyTo = false,
    ) {
    }
}
