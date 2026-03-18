<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Application;

use Contexis\Events\Communication\Domain\EmailDefinition;
use Contexis\Events\Communication\Domain\EmailDefinitionRepository;
use Contexis\Events\Communication\Domain\ValueObjects\EmailContext;

final readonly class ResolveEmailDefinition
{
    public function __construct(
        private EmailDefinitionRepository $emailDefinitionRepository,
    ) {
    }

    public function execute(EmailContext $context): ?EmailDefinition
    {
        $definitions = $this->emailDefinitionRepository->findApplicableForEvent($context->eventId);

        return $definitions->resolve($context);
    }
}
