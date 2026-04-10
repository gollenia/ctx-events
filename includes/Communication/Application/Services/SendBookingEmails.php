<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Services;

use Contexis\Events\Booking\Domain\ValueObjects\BookingId;
use Contexis\Events\Booking\Application\Contracts\BookingOptions;
use Contexis\Events\Communication\Application\Contracts\BookingEmailTrigger;
use Contexis\Events\Communication\Application\Contracts\EmailBodyRenderer;
use Contexis\Events\Communication\Application\Contracts\EmailSender;
use Contexis\Events\Communication\Application\Contracts\EventMailTemplateOverrideStore;
use Contexis\Events\Communication\Application\Contracts\EmailTemplateOverrideStore;
use Contexis\Events\Communication\Application\Contracts\EmailTemplatePresetProvider;
use Contexis\Events\Communication\Application\DTOs\BookingEmailDeliveryResult;
use Contexis\Events\Communication\Application\DTOs\BookingEmailResult;
use Contexis\Events\Communication\Application\ResolveEmailRecipient;
use Contexis\Events\Communication\Domain\EmailDefinition;
use Contexis\Events\Communication\Domain\EmailTemplatePreset;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Communication\Domain\ValueObjects\EmailAttachment;
use Contexis\Events\Communication\Domain\ValueObjects\AdminEmailRecipientConfig;
use Contexis\Events\Communication\Domain\ValueObjects\ResolvedEmail;
use Contexis\Events\Communication\Infrastructure\EmailTemplateTokenReplacer;
use Contexis\Events\Event\Application\Contracts\EventCalendarExporter;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

final readonly class SendBookingEmails implements BookingEmailTrigger
{
    public function __construct(
        private LoadBookingEmailContext $loadBookingEmailContext,
        private EmailTemplatePresetProvider $presetProvider,
        private EmailTemplateOverrideStore $overrideStore,
        private EventMailTemplateOverrideStore $eventOverrideStore,
        private EventCalendarExporter $eventCalendarExporter,
        private BookingOptions $bookingOptions,
        private EmailBodyRenderer $emailBodyRenderer,
        private EmailTemplateTokenReplacer $tokenReplacer,
        private ResolveEmailRecipient $resolveEmailRecipient,
        private EmailSender $emailSender,
    ) {
    }

    public function trigger(
        EmailTrigger $trigger,
        BookingId $bookingId,
        ?string $cancellationReason = null,
    ): BookingEmailResult
    {
        $presets = $this->presetProvider->all()->findByTrigger($trigger);

        if ($presets === []) {
            return BookingEmailResult::empty();
        }

        $context = $this->loadBookingEmailContext->load($bookingId, $cancellationReason);

        if ($context === null) {
            return $this->missingContextResult($presets);
        }

        $eventOverrides = $this->eventOverrideStore->eventMailTemplateOverrides($context->event->id);
        $attachments = $this->attachmentsFor($context->event);
        $result = BookingEmailResult::empty();

        foreach ($presets as $preset) {
            $result = $this->processPreset(
                $result,
                $preset,
                $eventOverrides[$preset->key->value] ?? null,
                $context,
                $attachments,
            );
        }

        return $result;
    }

    /**
     * @param list<EmailTemplatePreset> $presets
     */
    private function missingContextResult(array $presets): BookingEmailResult
    {
        $result = BookingEmailResult::empty();

        foreach ($presets as $preset) {
            $result = $result->withDelivery(new BookingEmailDeliveryResult(
                key: $preset->key,
                target: $preset->definition->target,
                status: BookingEmailDeliveryResult::STATUS_SKIPPED,
                reason: 'context_not_found',
            ));
        }

        return $result;
    }

    /**
     * @param array<string, mixed>|null $eventOverride
     * @param list<EmailAttachment> $attachments
     */
    private function processPreset(
        BookingEmailResult $result,
        EmailTemplatePreset $preset,
        ?array $eventOverride,
        \Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext $context,
        array $attachments,
    ): BookingEmailResult {
        [$definition, $recipientConfig] = $this->resolveTemplate($preset, $eventOverride);

        if (!$definition->enabled) {
            return $this->skippedResult($result, $preset, $definition, 'template_disabled');
        }

        $recipients = $this->resolveRecipients($definition, $recipientConfig, $context);

        if ($recipients === []) {
            return $this->skippedResult($result, $preset, $definition, 'recipient_not_resolved');
        }

        foreach ($recipients as $recipient) {
            $result = $this->deliverToRecipient(
                $result,
                $preset,
                $definition,
                $context,
                $recipient,
                $attachments,
            );
        }

        return $result;
    }

    private function skippedResult(
        BookingEmailResult $result,
        EmailTemplatePreset $preset,
        EmailDefinition $definition,
        string $reason,
    ): BookingEmailResult {
        return $result->withDelivery(new BookingEmailDeliveryResult(
            key: $preset->key,
            target: $definition->target,
            status: BookingEmailDeliveryResult::STATUS_SKIPPED,
            reason: $reason,
        ));
    }

    /**
     * @return list<Email>
     */
    private function resolveRecipients(
        EmailDefinition $definition,
        AdminEmailRecipientConfig $recipientConfig,
        \Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext $context,
    ): array {
        return $this->resolveEmailRecipient->executeMany(
            $definition->target,
            $context->booking,
            $context->event,
            $recipientConfig,
        );
    }

    /**
     * @param list<EmailAttachment> $attachments
     */
    private function deliverToRecipient(
        BookingEmailResult $result,
        EmailTemplatePreset $preset,
        EmailDefinition $definition,
        \Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext $context,
        Email $recipient,
        array $attachments,
    ): BookingEmailResult {
        $renderedBody = $this->emailBodyRenderer->render($definition->body, $context);
        $email = new ResolvedEmail(
            to: $recipient,
            subject: $this->tokenReplacer->replace($definition->subject ?? '', $context),
            body: $renderedBody->content,
            replyTo: $definition->replyTo,
            isHtml: $renderedBody->isHtml,
            attachments: $attachments,
        );

        try {
            $sent = $this->emailSender->send($email);
        } catch (\Throwable $exception) {
            return $this->failedResult($result, $preset, $definition, $recipient, 'send_exception');
        }

        if (!$sent) {
            return $this->failedResult($result, $preset, $definition, $recipient, 'send_failed');
        }

        return $result->withDelivery(new BookingEmailDeliveryResult(
            key: $preset->key,
            target: $definition->target,
            status: BookingEmailDeliveryResult::STATUS_SENT,
            recipient: $recipient,
        ));
    }

    private function failedResult(
        BookingEmailResult $result,
        EmailTemplatePreset $preset,
        EmailDefinition $definition,
        Email $recipient,
        string $reason,
    ): BookingEmailResult {
        return $result->withDelivery(new BookingEmailDeliveryResult(
            key: $preset->key,
            target: $definition->target,
            status: BookingEmailDeliveryResult::STATUS_FAILED,
            reason: $reason,
            recipient: $recipient,
        ));
    }

    /**
	 * @param array<string, mixed>|null $eventOverride
     * @return array{EmailDefinition, AdminEmailRecipientConfig}
     */
    private function resolveTemplate(EmailTemplatePreset $preset, ?array $eventOverride): array
    {
        $globalOverride = $this->overrideStore->emailTemplateOverrides()[$preset->key->value] ?? null;
        $override = array_replace(
            is_array($globalOverride) ? $globalOverride : [],
            is_array($eventOverride) ? $eventOverride : [],
        );
        $definition = $preset->definition;
        $recipientConfig = AdminEmailRecipientConfig::fromArray(
            is_array($override['recipientConfig'] ?? null) ? $override['recipientConfig'] : null,
            $definition->target,
        );

        return [
            new EmailDefinition(
                id: $definition->id,
                trigger: $definition->trigger,
                target: $definition->target,
                enabled: $this->overrideBool($override, 'enabled', $definition->enabled),
                eventId: $definition->eventId,
                gateway: $definition->gateway,
                subject: $this->overrideString($override, 'subject', $definition->subject),
                body: $this->overrideString($override, 'body', $definition->body) ?? '',
                replyTo: Email::tryFrom($this->overrideString($override, 'replyTo', $definition->replyTo?->toString())),
            ),
            $recipientConfig,
        ];
    }

    /**
     * @return list<EmailAttachment>
     */
    private function attachmentsFor(\Contexis\Events\Event\Domain\Event $event): array
    {
        if (!$this->bookingOptions->attachIcalToBookingEmail()) {
            return [];
        }

        $calendarFile = $this->eventCalendarExporter->export($event);

        return [
            new EmailAttachment(
                filename: $calendarFile->filename,
                mimeType: $calendarFile->mimeType,
                content: $calendarFile->content,
            ),
        ];
    }

    /**
     * @param array<string, mixed>|null $override
     */
    private function overrideString(?array $override, string $key, ?string $fallback): ?string
    {
        if (!is_array($override) || !array_key_exists($key, $override)) {
            return $fallback;
        }

        $value = $override[$key];

        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (string) $value : $fallback;
    }

    /**
     * @param array<string, mixed>|null $override
     */
    private function overrideBool(?array $override, string $key, bool $fallback): bool
    {
        if (!is_array($override) || !array_key_exists($key, $override)) {
            return $fallback;
        }

        return filter_var($override[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $fallback;
    }
}
