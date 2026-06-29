<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

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
            '{{booking.price}}' => $replace($this->formatPrice($context->booking->priceSummary->finalPrice)),
            '{{booking.cancellation_reason}}' => $replace($context->cancellationReason),
            '{{event.name}}' => $replace($context->event->name),
            '{{event.start}}' => $replace($context->event->startDate->format('Y-m-d H:i')),
            '{{event.end}}' => $replace($context->event->endDate->format('Y-m-d H:i')),
            '{{event.location}}' => $replace($context->eventLocationName),
            '{{event.speaker}}' => $replace($context->eventSpeakerName),
        ];
    }

    private function formatPrice(Price $price): string
    {
        $amount = number_format($price->toFloat(), 2, '.', '');

        return match ($price->currency->toString()) {
            'EUR' => $amount . ' EUR',
            default => $amount . ' ' . $price->currency->toString(),
        };
    }
}
