<?php

namespace Contexis\Events\Domain\Contracts;

interface WithPagination
{
	public function getTotalItems(): int;

	public function getTotalPages(): int;

	public function getCurrentPage(): int;

	public function getPerPage(): int;
}