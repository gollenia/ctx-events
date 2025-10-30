<?php

namespace Contexis\Events\Core\Contracts;

use Contexis\Events\Core\Container;
use Contexis\Events\Core\Request;

trait Http {
	protected function http(): Request{
		return Container::getInstance()->get(Request::class);
	}
}