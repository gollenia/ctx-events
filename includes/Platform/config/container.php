<?php

declare(strict_types=1);

use Contexis\Events\Platform\Demo\DumpSubscriber;
use Contexis\Events\Communication\Application\Contracts\BookingEmailTrigger;
use Contexis\Events\Communication\Application\Contracts\EmailBodyRenderer;
use Contexis\Events\Communication\Application\Contracts\EmailTemplatePresetProvider;
use Contexis\Events\Communication\Application\Contracts\EventMailTemplateOverrideStore;
use Contexis\Events\Communication\Application\Contracts\EmailSender;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Communication\Infrastructure\DefaultEmailTemplatePresetProvider;
use Contexis\Events\Communication\Infrastructure\EmailTemplateTokenReplacer;
use Contexis\Events\Communication\Infrastructure\TiptapEmailBodyRenderer;
use Contexis\Events\Communication\Infrastructure\WpEventMailTemplateOverrideStore;
use Contexis\Events\Communication\Infrastructure\WpEmailSender;
use Contexis\Events\Communication\Application\Services\SendBookingEmails;
use Contexis\Events\Event\Application\Contracts\EventCalendarExporter;
use Contexis\Events\Event\Application\Service\IcalEventCalendarExporter;
use Contexis\Events\Shared\Infrastructure\Wordpress\CurrentWordpressActorProvider;

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
	\Contexis\Events\Shared\Domain\Contracts\TokenGenerator::class
	=> autowire(\Contexis\Events\Shared\Infrastructure\Security\RandomTokenGenerator::class),
	\Contexis\Events\Shared\Domain\Contracts\HashGenerator::class
	=> autowire(\Contexis\Events\Shared\Infrastructure\Security\WpHashGenerator::class),
    \Contexis\Events\Shared\Domain\Contracts\SessionHashResolver::class
	=> autowire(\Contexis\Events\Shared\Infrastructure\Wordpress\WpSessionHashResolver::class),
    EmailTemplatePresetProvider::class => autowire(DefaultEmailTemplatePresetProvider::class),
    EmailTemplateTokenReplacer::class => autowire(),
    EmailBodyRenderer::class => autowire(TiptapEmailBodyRenderer::class),
    EventMailTemplateOverrideStore::class => autowire(WpEventMailTemplateOverrideStore::class),
    EventCalendarExporter::class => autowire(IcalEventCalendarExporter::class),
    EmailSender::class => autowire(WpEmailSender::class),
    BookingEmailTrigger::class => autowire(SendBookingEmails::class),

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
