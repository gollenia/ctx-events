<?php

namespace Contexis\Events\Core;

use Contexis\Events\Emails\Mailer;
use Contexis\Events\Notices;
use Contexis\Events\Payment\GatewayService;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Contexis\Events\Core\Http\Request;
use Contexis\Events\Application\Repositories\EventRepository;
use Contexis\Events\Infrastructure\Persistence\WpEventRepository;


class Bootstrap {

	public static function init(): void {
		(new \Contexis\Events\Infrastructure\WP\PostTypeRegistrar([
            \Contexis\Events\Infrastructure\PostTypes\EventPost::class,
            \Contexis\Events\Infrastructure\PostTypes\RecurringEventPost::class,
            \Contexis\Events\Infrastructure\PostTypes\LocationPost::class,
            \Contexis\Events\Infrastructure\PostTypes\OrganizerPost::class,
            \Contexis\Events\Infrastructure\PostTypes\SpeakerPost::class,
            \Contexis\Events\Infrastructure\PostTypes\VenuePost::class,
        ]))->hook();
		$symfonyRequest = SymfonyRequest::createFromGlobals();
		//$typedRequest = new Request($symfonyRequest);

		//$instance->app()->bind(Request::class, $typedRequest);
		$instance->app()->bind(SymfonyRequest::class, $symfonyRequest);

		$instance->app()->bind(GatewayService::class, function() {
			return new GatewayService();
		});

		$instance->app()->bind(Mailer::class, new Mailer());
		$instance->app()->bind(Notices::class, new Notices());
		$instance->app()->bind(EventRepository::class, WpEventRepository::class);
		
	}
}