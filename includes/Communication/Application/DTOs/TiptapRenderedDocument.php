<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\DTOs;

use Contexis\Events\Communication\Domain\ValueObjects\EmailInlineAttachment;

final readonly class TiptapRenderedDocument
{
    /** @param list<EmailInlineAttachment> $inlineAttachments */
    public function __construct(
        public string $html,
        public array $inlineAttachments = [],
    ) {
    }
}
