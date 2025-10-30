<?php

namespace Contexis\Events\Presentation\Admin;


class AdminService {

	private array $service_classes = [
		AdminMenu::class,
	];

	public static function init(): void {
		
		foreach ((new self())->service_classes as $service_class) {
			$service = new $service_class();
			add_action($service->hook, [$service, 'register']);
		}
	}
}