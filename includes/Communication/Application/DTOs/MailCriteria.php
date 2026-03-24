<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\DTOs;

use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Shared\Application\Contracts\Criteria;

final readonly class MailCriteria implements Criteria
{
    public function __construct(
        public ?string $search = null,
        public ?EmailTarget $target = null,
        public ?EmailTrigger $trigger = null,
    ) {
    }
}
