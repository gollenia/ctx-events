<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Domain\ValueObjects;

use Contexis\Events\Communication\Domain\Enums\EmailTarget;

final readonly class AdminEmailRecipientConfig
{
    /**
     * @param list<string> $customRecipients
     */
    public function __construct(
        public bool $sendToEventContact,
        public bool $sendToEventPerson,
        public bool $sendToBookingAdmin,
        public bool $sendToWpAdmin,
        public array $customRecipients,
    ) {
    }

    public static function defaultsFor(EmailTarget $target): self
    {
        if ($target === EmailTarget::ADMIN) {
            return new self(
                sendToEventContact: false,
                sendToEventPerson: false,
                sendToBookingAdmin: false,
                sendToWpAdmin: true,
                customRecipients: [],
            );
        }

        return new self(
            sendToEventContact: false,
            sendToEventPerson: false,
            sendToBookingAdmin: false,
            sendToWpAdmin: false,
            customRecipients: [],
        );
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public static function fromArray(?array $data, EmailTarget $target): self
    {
        $defaults = self::defaultsFor($target);

        if (!is_array($data)) {
            return $defaults;
        }

        $customRecipients = [];
        $rawRecipients = $data['customRecipients'] ?? [];

        if (is_array($rawRecipients)) {
            foreach ($rawRecipients as $recipient) {
                if (!is_scalar($recipient)) {
                    continue;
                }

                $value = trim((string) $recipient);
                if ($value !== '') {
                    $customRecipients[] = $value;
                }
            }
        }

        return new self(
            sendToEventContact: filter_var($data['sendToEventContact'] ?? $defaults->sendToEventContact, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $defaults->sendToEventContact,
            sendToEventPerson: filter_var($data['sendToEventPerson'] ?? $defaults->sendToEventPerson, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $defaults->sendToEventPerson,
            sendToBookingAdmin: filter_var($data['sendToBookingAdmin'] ?? $defaults->sendToBookingAdmin, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $defaults->sendToBookingAdmin,
            sendToWpAdmin: filter_var($data['sendToWpAdmin'] ?? $defaults->sendToWpAdmin, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $defaults->sendToWpAdmin,
            customRecipients: array_values(array_unique($customRecipients)),
        );
    }

    /**
     * @return array{
     *   sendToEventContact: bool,
     *   sendToEventPerson: bool,
     *   sendToBookingAdmin: bool,
     *   sendToWpAdmin: bool,
     *   customRecipients: list<string>
     * }
     */
    public function toArray(): array
    {
        return [
            'sendToEventContact' => $this->sendToEventContact,
            'sendToEventPerson' => $this->sendToEventPerson,
            'sendToBookingAdmin' => $this->sendToBookingAdmin,
            'sendToWpAdmin' => $this->sendToWpAdmin,
            'customRecipients' => $this->customRecipients,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->toArray() === $other->toArray();
    }
}
