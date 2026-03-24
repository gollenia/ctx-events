<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EmailSender;
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

        $attachments = $this->createTemporaryAttachments($email);

        try {
            return wp_mail(
                $email->to->toString(),
                $email->subject,
                $email->body,
                $headers,
                $attachments,
            );
        } finally {
            foreach ($attachments as $attachment) {
                if (is_string($attachment) && is_file($attachment)) {
                    @unlink($attachment);
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private function createTemporaryAttachments(ResolvedEmail $email): array
    {
        $files = [];

        foreach ($email->attachments as $index => $attachment) {
            $path = tempnam(sys_get_temp_dir(), 'ctx-events-mail-');

            if ($path === false) {
                continue;
            }

            @unlink($path);
            $sanitizedFilename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $attachment->filename) ?: 'attachment.bin';
            $targetPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('ctx-events-mail-', true) . '-' . $sanitizedFilename;

            if (file_put_contents($targetPath, $attachment->content) === false) {
                @unlink($targetPath);
                continue;
            }

            $files[] = $targetPath;
        }

        return $files;
    }
}
