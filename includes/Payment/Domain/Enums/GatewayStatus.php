<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain\Enums;

enum GatewayStatus: string
{
    case MissingConfiguration = 'missing_configuration';
    case Inactive = 'inactive';
    case Active = 'active';
    case TestMode = 'test_mode';
}
