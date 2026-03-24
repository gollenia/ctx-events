<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EmailBodyRenderer;
use Contexis\Events\Communication\Application\DTOs\RenderedEmailBody;
use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;

final readonly class TiptapEmailBodyRenderer implements EmailBodyRenderer
{
    public function __construct(
        private TiptapDocumentRenderer $documentRenderer,
        private EmailTemplateTokenReplacer $tokenReplacer,
    ) {
    }

    public function render(string $body, TriggeredEmailContext $context): RenderedEmailBody
    {
        if (!$this->documentRenderer->isTiptapDocument($body)) {
            return new RenderedEmailBody(
                content: $this->tokenReplacer->replaceText($body, $context),
                isHtml: false,
            );
        }

        return new RenderedEmailBody(
            content: $this->tokenReplacer->replaceHtml(
                $this->documentRenderer->renderToHtml($body, $context),
                $context,
            ),
            isHtml: true,
        );
    }
}
