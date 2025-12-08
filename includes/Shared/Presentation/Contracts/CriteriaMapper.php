<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Presentation\Contracts;

use Contexis\Events\Shared\Application\ValueObjects\UserContext;

interface CriteriaMapper
{
    public static function fromRequest(\WP_REST_Request $request, UserContext $userContext): mixed;
}
