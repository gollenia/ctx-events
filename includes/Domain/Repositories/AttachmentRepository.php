<?php

namespace Contexis\Events\Domain\Repositories;

use Contexis\Events\Domain\ValueObjects\Id\AttachmentId;
use Contexis\Events\Domain\ValueObjects\Attachment;

interface AttachmentRepository
{
    public function find(?AttachmentId $id): ?Attachment;
}
