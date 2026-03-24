<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application\Contracts;

use Contexis\Events\Communication\Domain\EmailTemplatePresetCollection;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Domain\EmailTemplatePreset;

interface EmailTemplatePresetProvider
{
    public function all(): EmailTemplatePresetCollection;

    public function find(EmailTemplateKey $key): ?EmailTemplatePreset;
}
