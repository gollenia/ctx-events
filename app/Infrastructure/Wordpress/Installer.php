<?php

namespace Contexis\Events\Infrastructure\Wordpress;

use Contexis\Events\Infrastructure\Persistence\Migration\BookingMigration;

class Installer {

	

	public static function install(): void
	{
		Migration::run();
		

	}
	
}