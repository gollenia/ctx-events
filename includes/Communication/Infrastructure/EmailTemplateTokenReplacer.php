<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;

final class EmailTemplateTokenReplacer
{
    public function replace(string $content, TriggeredEmailContext $context): string
    {
        return strtr($content, $this->replacements($context, false));
    }

    public function replaceText(string $content, TriggeredEmailContext $context): string
    {
        return $this->replace($content, $context);
    }

    public function replaceHtml(string $content, TriggeredEmailContext $context): string
    {
        return strtr($content, $this->replacements($context, true));
    }

    /**
     * @return array<string, string>
     */
    private function replacements(TriggeredEmailContext $context, bool $escapeHtml): array
    {
        $replace = static fn (?string $value): string => $escapeHtml
            ? htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            : ($value ?? '');

        return [
            '{{booking.reference}}' => $replace($context->booking->reference->toString()),
            '{{booking.email}}' => $replace($context->booking->email->toString()),
            '{{booking.first_name}}' => $replace($context->booking->name->firstName),
            '{{booking.last_name}}' => $replace($context->booking->name->lastName),
            '{{event.name}}' => $replace($context->event->name),
            '{{event.start}}' => $replace($context->event->startDate->format('Y-m-d H:i')),
            '{{event.end}}' => $replace($context->event->endDate->format('Y-m-d H:i')),
        ];
    }
}
