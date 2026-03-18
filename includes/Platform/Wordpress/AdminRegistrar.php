<?php

declare(strict_types=1);

namespace Contexis\Events\Platform\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Registrar;
use Contexis\Events\Shared\Presentation\Contracts\AdminService;

class AdminRegistrar implements Registrar
{
	/** @var AdminService[] */
	private array $admin_services;

	/**
	 * @param AdminService[] $admin_services
	 */
	public function __construct(array $admin_services)
	{
		$this->admin_services = $admin_services;
	}

	public function hook(): void
	{
		foreach ($this->admin_services as $admin_service) {
			$admin_service->hook();
		}
	}
}
