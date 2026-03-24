<?php
declare(strict_types=1);

namespace Tests\Support;

use Contexis\Events\Communication\Application\Contracts\EmailSender;
use Contexis\Events\Communication\Domain\ValueObjects\ResolvedEmail;

final class FakeEmailSender implements EmailSender
{
    public ?ResolvedEmail $lastEmail = null;
    public bool $shouldSucceed = true;
    public ?\Throwable $exception = null;
    /** @var list<ResolvedEmail> */
    public array $sentEmails = [];

    public function send(ResolvedEmail $email): bool
    {
        $this->lastEmail = $email;
        $this->sentEmails[] = $email;

        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->shouldSucceed;
    }
}
