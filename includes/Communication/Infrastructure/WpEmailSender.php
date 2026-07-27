<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EmailSender;
use Contexis\Events\Communication\Domain\ValueObjects\EmailAttachment;
use Contexis\Events\Communication\Domain\ValueObjects\EmailInlineAttachment;
use Contexis\Events\Communication\Domain\ValueObjects\ResolvedEmail;

final class WpEmailSender implements EmailSender
{
    public function send(ResolvedEmail $email): bool
    {
        $headers = [
            $email->isHtml
                ? 'Content-Type: text/html; charset=UTF-8'
                : 'Content-Type: text/plain; charset=UTF-8',
        ];

        if ($email->replyTo !== null) {
            $headers[] = 'Reply-To: ' . $email->replyTo->toString();
        }

        $attachments = $this->createTemporaryAttachments($email->attachments);
        $inlineAttachments = $this->createTemporaryInlineAttachments($email->inlineAttachments);
        $embedInlineAttachments = static function ($mailer) use ($inlineAttachments): void {
            foreach ($inlineAttachments as $attachment) {
                $mailer->addEmbeddedImage(
                    $attachment['path'],
                    $attachment['contentId'],
                    $attachment['filename'],
                    'base64',
                    $attachment['mimeType'],
                );
            }
        };

        if ($inlineAttachments !== []) {
            add_action('phpmailer_init', $embedInlineAttachments);
        }

        try {
            return wp_mail(
                $email->to->toString(),
                $email->subject,
                $email->body,
                $headers,
                $attachments,
            );
        } finally {
            if ($inlineAttachments !== []) {
                remove_action('phpmailer_init', $embedInlineAttachments);
            }

            foreach (array_merge($attachments, array_column($inlineAttachments, 'path')) as $attachment) {
                if (is_string($attachment) && is_file($attachment)) {
                    @unlink($attachment);
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private function createTemporaryAttachments(array $attachments): array
    {
        $files = [];

        foreach ($attachments as $attachment) {
            if (!$attachment instanceof EmailAttachment) {
                continue;
            }

            $path = $this->createTemporaryFile($attachment->filename, $attachment->content);
            if ($path !== null) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * @param list<EmailInlineAttachment> $attachments
     * @return list<array{path: string, contentId: string, filename: string, mimeType: string}>
     */
    private function createTemporaryInlineAttachments(array $attachments): array
    {
        $files = [];

        foreach ($attachments as $attachment) {
            $path = $this->createTemporaryFile($attachment->filename, $attachment->content);
            if ($path === null) {
                continue;
            }

            $files[] = [
                'path' => $path,
                'contentId' => $attachment->contentId,
                'filename' => $attachment->filename,
                'mimeType' => $attachment->mimeType,
            ];
        }

        return $files;
    }

    private function createTemporaryFile(string $filename, string $content): ?string
    {
        $path = tempnam(sys_get_temp_dir(), 'ctx-events-mail-');
        if ($path === false) {
            return null;
        }

        @unlink($path);
        $sanitizedFilename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?: 'attachment.bin';
        $targetPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('ctx-events-mail-', true) . '-' . $sanitizedFilename;

        if (file_put_contents($targetPath, $content) === false) {
            @unlink($targetPath);
            return null;
        }

        return $targetPath;
    }
}
