<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Contracts;

use Contexis\Events\Communication\Application\DTOs\RenderedEmailBody;
use Contexis\Events\Communication\Application\DTOs\TriggeredEmailContext;

interface EmailBodyRenderer
{
    public function render(string $body, TriggeredEmailContext $context): RenderedEmailBody;
}
