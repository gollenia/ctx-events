<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain\ValueObjects;

use Contexis\Events\Shared\Domain\ValueObjects\Email;

final readonly class ResolvedEmail
{
    /** @param list<EmailAttachment> $attachments */
    /** @param list<EmailInlineAttachment> $inlineAttachments */
    public function __construct(
        public Email $to,
        public string $subject,
        public string $body,
        public ?Email $replyTo = null,
        public bool $isHtml = false,
        public array $attachments = [],
        public array $inlineAttachments = [],
    ) {
    }
}
