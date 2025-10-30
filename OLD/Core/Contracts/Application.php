<?php

namespace Contexis\Events\Core\Contracts;

use Contexis\Events\Core\Container;

trait Application {
	protected function app(): Container {
		return Container::getInstance();
	}
}