<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\DTOs;

use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Communication\Domain\ValueObjects\AdminEmailRecipientConfig;

final readonly class MailListItem
{
    public function __construct(
        public EmailTemplateKey $key,
        public string $label,
        public string $description,
        public EmailTrigger $trigger,
        public EmailTarget $target,
        public string $source,
        public bool $isCustomized,
        public bool $enabled,
        public ?string $subject,
        public string $body,
        public ?string $replyTo = null,
        public ?AdminEmailRecipientConfig $recipientConfig = null,
    ) {
    }
}
