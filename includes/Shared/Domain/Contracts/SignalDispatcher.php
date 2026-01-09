<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Contracts;

use Contexis\Events\Shared\Domain\Abstract\Signal;

interface SignalDispatcher
{
    public function dispatch(Signal $signal): void;
}