<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Contracts;

use Contexis\Events\Communication\Domain\ValueObjects\ResolvedEmail;

interface EmailSender
{
    public function send(ResolvedEmail $email): bool;
}
