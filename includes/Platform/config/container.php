<?php

declare(strict_types=1);

use Contexis\Events\Platform\Demo\DumpSubscriber;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Domain\Contracts\SignalDispatcher;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Shared\Infrastructure\Wordpress\CurrentWordpressActorProvider;
use Contexis\Events\Shared\Infrastructure\Wordpress\WordpressSignalDispatcher;
use Psr\Container\ContainerInterface;

use function DI\autowire;
use function DI\create;
use function DI\get;

$posttypes = require_once __DIR__ . '/posttypes.php';
$controllers = require_once __DIR__ . '/controllers.php';
$migrations = require_once __DIR__ . '/migrations.php';
$options = require_once __DIR__ . '/options.php';
$repositories = require_once __DIR__ . '/repositories.php';
$admin = require_once __DIR__ . '/admin.php';
$events = require_once __DIR__ . '/events.php';
$hooks = require_once __DIR__ . '/hooks.php';

return [

    \Contexis\Events\Shared\Domain\Contracts\Clock::class
    => autowire(\Contexis\Events\Shared\Infrastructure\Wordpress\SystemClock::class),
    CurrentActorProvider::class => autowire(CurrentWordpressActorProvider::class),
	SignalDispatcher::class => autowire(WordpressSignalDispatcher::class),
	\Contexis\Events\Shared\Domain\Contracts\TokenGenerator::class
	=> autowire(\Contexis\Events\Shared\Infrastructure\Security\RandomTokenGenerator::class),
	\Contexis\Events\Shared\Domain\Contracts\HashGenerator::class
	=> autowire(\Contexis\Events\Shared\Infrastructure\Security\WpHashGenerator::class),
	\Contexis\Events\Shared\Domain\Contracts\SessionHashResolver::class
	=> autowire(\Contexis\Events\Shared\Infrastructure\Wordpress\WpSessionHashResolver::class),

	Database::class => autowire(\Contexis\Events\Shared\Infrastructure\Wordpress\WpDatabase::class),
	
    \Contexis\Events\Platform\Wordpress\PostTypeRegistrar::class => autowire()->constructor($posttypes),
    \Contexis\Events\Platform\Wordpress\RestRegistrar::class   => autowire()->constructor($controllers),
    \Contexis\Events\Platform\Wordpress\DatabaseMigration::class => autowire()->constructor($migrations),
    \Contexis\Events\Platform\Wordpress\AdminRegistrar::class => create()->constructor($admin),
    \Contexis\Events\Platform\Wordpress\HookRegistrar::class => create()->constructor($hooks),

	...$options,
	...$repositories,
	...$events,
];
