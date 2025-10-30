<?php

namespace Contexis\Events\Core\ServiceProviders;

use Contexis\Events\Core\App;
use Contexis\Events\Core\Contracts\ServiceProvider;
use Contexis\Events\Presentation\REST\RestAdapter;

final class EventServiceProvider extends ServiceProvider {
	public function register(): void {
        App::set(\Contexis\Events\Domain\Repository\EventRepository::class,
            fn() => new \Contexis\Events\Infrastructure\Persistence\WpEventRepository());

        App::get::(RestAdapter::class)->add(new \Contexis\Events\Presentation\REST\EventController());
    }
}
