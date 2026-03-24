<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\DTOs;

use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

final readonly class BookingEmailDeliveryResult
{
    public const STATUS_SENT = 'sent';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_FAILED = 'failed';

    public function __construct(
        public EmailTemplateKey $key,
        public EmailTarget $target,
        public string $status,
        public ?string $reason = null,
        public ?Email $recipient = null,
    ) {
    }

    public function failed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
