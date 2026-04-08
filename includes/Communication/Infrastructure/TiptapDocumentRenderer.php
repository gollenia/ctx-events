<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;
use Contexis\Events\Communication\Infrastructure\Tiptap\AttendeeTableNode;
use Contexis\Events\Communication\Infrastructure\Tiptap\MailTokenNode;
use Contexis\Events\Communication\Infrastructure\Tiptap\RegistrationDataNode;
use Contexis\Events\Communication\Infrastructure\Tiptap\TextColorMark;
use Contexis\Events\Communication\Infrastructure\Tiptap\UnderlineMark;
use Tiptap\Editor;
use Tiptap\Extensions\StarterKit;

final class TiptapDocumentRenderer
{
    public function isTiptapDocument(string $body): bool
    {
        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return false;
        }

        return is_array($decoded) && ($decoded['type'] ?? null) === 'doc';
    }

    public function renderToHtml(string $body, TriggeredEmailContext $context): string
    {
        return (new Editor([
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
            ],
        ]))
            ->setContent($body)
            ->getHTML();
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

    private function humanizeKey(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}
