<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;
use Contexis\Events\Communication\Application\DTOs\TiptapRenderedDocument;
use Contexis\Events\Communication\Application\UseCases\ResolveEmailPaymentInformation;
use Contexis\Events\Communication\Infrastructure\Tiptap\AttendeeTableNode;
use Contexis\Events\Communication\Infrastructure\Tiptap\MailTokenNode;
use Contexis\Events\Communication\Infrastructure\Tiptap\PaymentInformationNode;
use Contexis\Events\Communication\Infrastructure\Tiptap\RegistrationDataNode;
use Contexis\Events\Communication\Infrastructure\Tiptap\TextColorMark;
use Contexis\Events\Communication\Infrastructure\Tiptap\UnderlineMark;
use Contexis\Events\Communication\Domain\ValueObjects\EmailInlineAttachment;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Tiptap\Editor;
use Tiptap\Extensions\StarterKit;

final class TiptapDocumentRenderer
{
    public function __construct(
        private readonly ?ResolveEmailPaymentInformation $paymentInformation = null,
    ) {
    }
    public function isTiptapDocument(string $body): bool
    {
        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return false;
        }

        return is_array($decoded) && ($decoded['type'] ?? null) === 'doc';
    }

    public function render(string $body, TriggeredEmailContext $context): TiptapRenderedDocument
    {
        $inlineAttachments = [];
        $html = (new Editor([
            'extensions' => [
                new StarterKit([
                    'heading' => false,
                    'blockquote' => false,
                    'codeBlock' => false,
                    'horizontalRule' => false,
                    'strike' => false,
                ]),
                new UnderlineMark(),
                new TextColorMark(),
                new MailTokenNode([
                    'resolveToken' => function (string $token) use ($context): string {
                        return (new EmailTemplateTokenReplacer())->replaceHtml($token, $context);
                    },
                ]),
                new RegistrationDataNode([
                    'renderRegistrationData' => fn (): string => $this->renderRegistrationData($context),
                ]),
                new AttendeeTableNode([
                    'renderAttendeeTable' => fn (): string => $this->renderAttendeeTable($context),
                ]),
                new PaymentInformationNode([
                    'renderPaymentInformation' => function () use ($context, &$inlineAttachments): string {
                        return $this->renderPaymentInformation($context, $inlineAttachments);
                    },
                ]),
            ],
        ]))
            ->setContent($body)
            ->getHTML();

        return new TiptapRenderedDocument($html, $inlineAttachments);
    }

    private function renderRegistrationData(TriggeredEmailContext $context): string
    {
        $rows = [];

        foreach ($context->booking->registration->all() as $key => $value) {
            if (!is_scalar($value) || $value === '') {
                continue;
            }

            $rows[] = sprintf(
                '<tr><th style="text-align:left;padding:6px 10px;border:1px solid #dcdcde;">%s</th><td style="padding:6px 10px;border:1px solid #dcdcde;">%s</td></tr>',
                htmlspecialchars($this->humanizeKey((string) $key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            );
        }

        if ($rows === []) {
            return '';
        }

        return '<table style="border-collapse:collapse;width:100%;margin:12px 0;"><tbody>' . implode('', $rows) . '</tbody></table>';
    }

    private function renderAttendeeTable(TriggeredEmailContext $context): string
    {
        $rows = [];
        $ticketNames = [];

        foreach ($context->event->tickets ?? [] as $ticket) {
            $ticketNames[$ticket->id->toString()] = $ticket->name;
        }

        foreach ($context->attendees as $attendee) {
            $name = trim(implode(' ', array_filter([
                $attendee->name?->firstName ?? null,
                $attendee->name?->lastName ?? null,
            ])));

            $rows[] = sprintf(
                '<tr><td style="padding:6px 10px;border:1px solid #dcdcde;">%s</td><td style="padding:6px 10px;border:1px solid #dcdcde;">%s</td><td style="padding:6px 10px;border:1px solid #dcdcde;">%s</td></tr>',
                htmlspecialchars($name !== '' ? $name : '-', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars($ticketNames[$attendee->ticketId->toString()] ?? $attendee->ticketId->toString(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars($attendee->birthDate?->format('Y-m-d') ?? '-', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            );
        }

        if ($rows === []) {
            return '';
        }

        return '<table style="border-collapse:collapse;width:100%;margin:12px 0;"><thead><tr><th style="text-align:left;padding:6px 10px;border:1px solid #dcdcde;">Name</th><th style="text-align:left;padding:6px 10px;border:1px solid #dcdcde;">Ticket</th><th style="text-align:left;padding:6px 10px;border:1px solid #dcdcde;">Birth date</th></tr></thead><tbody>' . implode('', $rows) . '</tbody></table>';
    }

    /** @param list<EmailInlineAttachment> $inlineAttachments */
    private function renderPaymentInformation(TriggeredEmailContext $context, array &$inlineAttachments): string
    {
        if ($this->paymentInformation === null) {
            return '';
        }

        $payment = $this->paymentInformation->execute($context->booking);
        if ($payment === null) {
            return '';
        }

        $transaction = $payment->transaction;
        $rows = [
            $this->paymentRow('Payment method', $payment->gatewayTitle),
            $this->paymentRow('Amount', $this->formatPrice($transaction->amount)),
        ];

        if ($transaction->expiresAt !== null) {
            $rows[] = $this->paymentRow('Pay by', $transaction->expiresAt->format('d.m.Y'));
        }

        if ($transaction->isOffline() && $transaction->bankData !== null) {
            $bankData = $transaction->bankData;
            $rows[] = $this->paymentRow('Account holder', $bankData->accountHolder);
            $rows[] = $this->paymentRow('Bank', $bankData->bankName);
            $rows[] = $this->paymentRow('IBAN', $bankData->iban);
            $rows[] = $this->paymentRow('BIC', $bankData->bic);
            $rows[] = $this->paymentRow('Reference', $context->booking->reference->toString());
        }

        $content = '<table style="border-collapse:collapse;width:100%;margin:12px 0;"><tbody>' . implode('', $rows) . '</tbody></table>';

        if ($transaction->checkoutUrl !== null) {
            $content .= sprintf(
                '<p style="margin:16px 0;"><a href="%s" style="display:inline-block;padding:10px 16px;background:#2271b1;color:#ffffff;text-decoration:none;border-radius:3px;">Pay now</a></p>',
                htmlspecialchars($transaction->checkoutUrl->toString(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            );
        }

        if ($payment->qrCodePng !== null) {
            $contentId = 'ctx-payment-qr-' . md5($context->booking->reference->toString()) . '@ctx-events';
            $alreadyAttached = array_any(
                $inlineAttachments,
                static fn (EmailInlineAttachment $attachment): bool => $attachment->contentId === $contentId,
            );

            if (!$alreadyAttached) {
                $inlineAttachments[] = new EmailInlineAttachment(
                    contentId: $contentId,
                    filename: 'payment-qr.png',
                    mimeType: 'image/png',
                    content: $payment->qrCodePng,
                );
            }
            $content .= sprintf(
                '<p style="margin:16px 0;"><img src="cid:%s" width="180" height="180" alt="Payment QR code" style="display:block;width:180px;height:180px;" /></p>',
                htmlspecialchars($contentId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            );
        }

        return $content;
    }

    private function paymentRow(string $label, string $value): string
    {
        return sprintf(
            '<tr><th style="text-align:left;padding:6px 10px;border:1px solid #dcdcde;">%s</th><td style="padding:6px 10px;border:1px solid #dcdcde;">%s</td></tr>',
            htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }

    private function formatPrice(Price $price): string
    {
        return number_format($price->toFloat(), 2, '.', '') . ' ' . $price->currency->toString();
    }

    private function humanizeKey(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}
