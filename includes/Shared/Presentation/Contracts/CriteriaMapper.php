<?php

namespace Contexis\Events\Shared\Presentation\Contracts;

interface CriteriaMapper
{
    public static function fromRequest(\WP_REST_Request $request): mixed;
}
