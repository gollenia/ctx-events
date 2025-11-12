<?php

namespace Contexis\Events\Domain\Contracts;

interface WithOrder {
	public function getOrderBy(): string;

	public function getOrderDirection(): string;
}