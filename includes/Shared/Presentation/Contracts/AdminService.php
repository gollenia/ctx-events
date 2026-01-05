<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Presentation\Contracts;

interface AdminService
{
	public function hook(): void;
}