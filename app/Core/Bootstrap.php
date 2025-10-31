<?php

namespace Contexis\Events\Core;

use Contexis\Events\Infrastructure\Wordpress\Installer;
use Contexis\Events\Presentation\Admin\AdminMenu;
use Contexis\Events\Presentation\Admin\AdminService;
use Contexis\Events\Presentation\REST\RestRegistrar;
use DI\ContainerBuilder;

class Bootstrap {

	public static function init(): void {

		Installer::init();
		(new \Contexis\Events\Infrastructure\PostTypes\PostTypeRegistrar([
			\Contexis\Events\Infrastructure\PostTypes\EventPost::class,
		]))->hook();

		
		App::get_container();
		App::get(RestRegistrar::class)->hook();
		
	}
}