<?php

declare(strict_types=1);

use Contexis\Events\Platform\Demo\DumpSubscriber;
use Contexis\Events\Communication\Application\Contracts\BookingEmailTrigger;
use Contexis\Events\Communication\Application\Contracts\EmailBodyRenderer;
use Contexis\Events\Communication\Application\Contracts\EmailTemplatePresetProvider;
use Contexis\Events\Communication\Application\Contracts\EventMailTemplateOverrideStore;
use Contexis\Events\Communication\Application\Contracts\EventMailSettingsProvider;
use Contexis\Events\Communication\Application\Contracts\EmailSender;
use Contexis\Events\Shared\Domain\Contracts\CurrentActorProvider;
use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Communication\Infrastructure\DefaultEmailTemplatePresetProvider;
use Contexis\Events\Communication\Infrastructure\EmailTemplateTokenReplacer;
use Contexis\Events\Communication\Infrastructure\TiptapEmailBodyRenderer;
use Contexis\Events\Communication\Infrastructure\TiptapDocumentRenderer;
use Contexis\Events\Communication\Application\UseCases\ResolveEmailPaymentInformation;
use Contexis\Events\Communication\Infrastructure\WpEventMailTemplateOverrideStore;
use Contexis\Events\Communication\Infrastructure\WpEventMailSettingsProvider;
use Contexis\Events\Communication\Infrastructure\WpEmailSender;
use Contexis\Events\Communication\Application\Services\SendBookingEmails;
use Contexis\Events\Event\Application\Contracts\EventCalendarExporter;
use Contexis\Events\Event\Application\Service\IcalEventCalendarExporter;
use Contexis\Events\Shared\Infrastructure\Wordpress\CurrentWordpressActorProvider;
use Contexis\Events\Booking\Application\Services\SynchronizeBookingPaymentBalance;
use Contexis\Events\Booking\Application\Services\CancelOpenBookingTransactions;
use Contexis\Events\Booking\Application\Services\CancelBookingsForEvent;
use Contexis\Events\Booking\Application\UseCases\AddBookingAttendee;
use Contexis\Events\Booking\Application\UseCases\CancelBooking;
use Contexis\Events\Booking\Application\UseCases\CancelBookingAttendee;
use Contexis\Events\Booking\Application\UseCases\CreateBooking;
use Contexis\Events\Booking\Application\UseCases\UpdateBooking;
use Contexis\Events\Booking\Application\UseCases\UpdateBookingAttendee;

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
    TiptapDocumentRenderer::class => autowire()->constructor(get(ResolveEmailPaymentInformation::class)),
    EmailBodyRenderer::class => autowire(TiptapEmailBodyRenderer::class),
    EventMailTemplateOverrideStore::class => autowire(WpEventMailTemplateOverrideStore::class),
    EventMailSettingsProvider::class => autowire(WpEventMailSettingsProvider::class),
    EventCalendarExporter::class => autowire(IcalEventCalendarExporter::class),
    EmailSender::class => autowire(WpEmailSender::class),
    BookingEmailTrigger::class => autowire(SendBookingEmails::class),
    SynchronizeBookingPaymentBalance::class => autowire(),
    CancelOpenBookingTransactions::class => autowire(),
    AddBookingAttendee::class => autowire()
        ->constructorParameter('paymentBalanceSynchronizer', get(SynchronizeBookingPaymentBalance::class)),
    CancelBookingAttendee::class => autowire()
        ->constructorParameter('paymentBalanceSynchronizer', get(SynchronizeBookingPaymentBalance::class)),
    UpdateBookingAttendee::class => autowire()
        ->constructorParameter('paymentBalanceSynchronizer', get(SynchronizeBookingPaymentBalance::class)),
    UpdateBooking::class => autowire()
        ->constructorParameter('paymentBalanceSynchronizer', get(SynchronizeBookingPaymentBalance::class)),
    CancelBooking::class => autowire()
        ->constructorParameter('openTransactionCanceler', get(CancelOpenBookingTransactions::class)),
    CreateBooking::class => autowire(),
    CancelBookingsForEvent::class => autowire()
        ->constructorParameter('openTransactionCanceler', get(CancelOpenBookingTransactions::class)),

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
