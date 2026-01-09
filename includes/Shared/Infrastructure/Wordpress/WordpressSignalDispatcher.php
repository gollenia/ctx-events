<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Domain\Contracts\SignalDispatcher;
use Contexis\Events\Shared\Domain\Abstract\Signal;
use Psr\EventDispatcher\EventDispatcherInterface;

final class WordpressSignalDispatcher implements SignalDispatcher
{
    public function __construct(
        private readonly EventDispatcherInterface $engine
    ) {}

    public function dispatch(Signal $signal): void
    {
		$this->engine->dispatch($signal);
		if (defined(get_class($signal) . '::NAME')) {
			do_action($signal::NAME, $signal);
		}
    }
}