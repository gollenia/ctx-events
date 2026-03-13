<?php
declare(strict_types=1);

use Contexis\Events\Booking\Domain\ValueObjects\BookingReference;
use Contexis\Events\Booking\Domain\ValueObjects\PriceSummary;
use Contexis\Events\Booking\Domain\ValueObjects\RegistrationData;
use Contexis\Events\Event\Domain\ValueObjects\EventForms;
use Contexis\Events\Event\Domain\ValueObjects\EventSpaces;
use Contexis\Events\Event\Domain\ValueObjects\EventStatusCounts;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\ValueObjects\CountryCodes;
use Contexis\Events\Form\Domain\ValueObjects\SelectOption;
use Contexis\Events\Form\Domain\ValueObjects\SelectOptions;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Application\ValueObjects\Taxonomy;
use Contexis\Events\Shared\Application\ValueObjects\TaxonomyCollection;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;
use Contexis\Events\Shared\Domain\ValueObjects\Address;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Domain\ValueObjects\Link;
use Contexis\Events\Shared\Domain\ValueObjects\PersonName;
use Contexis\Events\Shared\Domain\ValueObjects\Price;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\StatusCounts;
use Contexis\Events\Shared\Domain\ValueObjects\StatusList;
use Contexis\Events\Shared\Infrastructure\ValueObjects\Order;
use Contexis\Events\Shared\Infrastructure\ValueObjects\OrderBy;

test('link enforces valid url and normalizes http to https', function () {
    $link = Link::fromString('http://example.com/path');
    expect((string) $link)->toBe('https://example.com/path');

    expect(static fn () => Link::fromString('not-a-url'))->toThrow(InvalidArgumentException::class);
});

test('email tryFrom validates and normalizes nullability', function () {
    expect(Email::tryFrom(null))->toBeNull();
    expect(Email::tryFrom(''))->toBeNull();
    expect(Email::tryFrom('invalid'))->toBeNull();

    $email = Email::tryFrom(' test@example.com ');
    expect($email?->address())->toBe('test@example.com');
    expect($email?->isValid())->toBeTrue();
});

test('booking reference enforces 12 character alphanumeric format', function () {
    $reference = BookingReference::fromString('A1B2C3D4E5F6');
    expect($reference->toString())->toBe('A1B2C3D4E5F6');

    expect(static fn () => BookingReference::fromString('short'))->toThrow(InvalidArgumentException::class);
});

test('select option and options parse, contains and serialize values', function () {
    $arrayOption = SelectOption::fromMixed(['label' => 'Adult', 'value' => 'adult']);
    $stringOption = SelectOption::fromMixed('Child');
    $options = SelectOptions::fromArray([
        ['label' => 'Adult', 'value' => 'adult'],
        'Child',
    ]);

    expect($arrayOption->getEffectiveValue())->toBe('adult');
    expect($stringOption->getEffectiveValue())->toBe('Child');
    expect($options->contains('adult'))->toBeTrue();
    expect($options->contains('Child'))->toBeTrue();
    expect($options->contains('senior'))->toBeFalse();
    expect($options->count())->toBe(2);
});

test('registration data returns string values from scalar payloads', function () {
    $registration = new RegistrationData([
        'email' => 'person@example.com',
        'age' => 42,
        'meta' => ['nested' => true],
    ]);

    expect($registration->getString('email'))->toBe('person@example.com');
    expect($registration->getString('age'))->toBe('42');
    expect($registration->getString('meta'))->toBeNull();
    expect($registration->getString('missing'))->toBeNull();
});

test('event spaces computes availability sold out and overbooked', function () {
    $spaces = new EventSpaces(capacity: 10, confirmed: 7, pending: 2, rejected: 1, expired: 0, canceled: 0);
    expect($spaces->available())->toBe(1);
    expect($spaces->isSoldOut())->toBeFalse();
    expect($spaces->hasSpaces())->toBeTrue();
    expect($spaces->isOverbooked())->toBeFalse();

    $overbooked = new EventSpaces(capacity: 10, confirmed: 9, pending: 3, rejected: 0, expired: 0, canceled: 0);
    expect($overbooked->isSoldOut())->toBeTrue();
    expect($overbooked->isOverbooked())->toBeTrue();
});

test('status list factories and conversion methods behave as expected', function () {
    $public = StatusList::public();
    $defaultAdmin = StatusList::defaultAdmin();
    $fromStrings = StatusList::fromStrings(['publish', 'draft']);

    expect($public->toArray())->toBe(['publish']);
    expect($defaultAdmin->toArray())->toBe(['publish', 'future', 'draft', 'private']);
    expect($fromStrings->toArray())->toBe(['publish', 'draft']);
    expect(StatusList::of()->isEmpty())->toBeTrue();
});

test('order and order by create sortable configuration', function () {
    $parsed = Order::ASC->fromString('DESC');
    $orderBy = OrderBy::fromMeta('booking_start', Order::ASC);
    $changedOrder = $orderBy->withOrder(Order::DESC);

    expect($parsed)->toBe(Order::DESC);
    expect($orderBy->field)->toBe('booking_start');
    expect($orderBy->isMeta)->toBeTrue();
    expect($changedOrder->order)->toBe(Order::DESC);

    expect(static fn () => Order::ASC->fromString('invalid'))->toThrow(InvalidArgumentException::class);
});

test('pagination computes total pages and exposes empty values', function () {
    $pagination = Pagination::of(totalItems: 55, currentPage: 2, perPage: 10);
    expect($pagination->totalPages())->toBe(6);

    $empty = Pagination::empty();
    expect($empty->currentPage)->toBe(1);
    expect($empty->totalItems)->toBe(0);
});

test('country codes normalize, deduplicate and allow-all on empty list', function () {
    $codes = new CountryCodes(['at', 'AT', 'de']);
    expect($codes->toArray())->toBe(['AT', 'DE']);
    expect($codes->contains('at'))->toBeTrue();
    expect($codes->contains('FR'))->toBeFalse();

    $allowAll = CountryCodes::of();
    expect($allowAll->contains('ANY'))->toBeTrue();
});

test('address createOrNot returns null for empty input', function () {
    $empty = Address::createOrNot(null, null, null, null, null, null);
    expect($empty)->toBeNull();

    $address = Address::createOrNot('Street 1', null, 'City', null, '1000', 'AT');
    expect($address)->not->toBeNull();
    expect($address?->isEmpty())->toBeFalse();
});

test('person name and user context helper methods work', function () {
    $name = PersonName::fromFullName('John Doe');
    expect($name->firstName)->toBe('John');
    expect($name->lastName)->toBe('Doe');
    expect($name->toString())->toBe('John Doe');

    $context = new UserContext(userId: 12, canView: true, canEdit: false, canManageOptions: true);
    expect($context->isAnonymous())->toBeFalse();
    expect($context->canView())->toBeTrue();
    expect($context->canEdit())->toBeFalse();
    expect($context->isAdmin())->toBeTrue();
});

test('price summary, status counts, event status counts and event forms work as expected', function () {
    $summary = PriceSummary::fromValues(
		new Price(1000, Currency::fromCode('EUR')), 
		new Price(200, Currency::fromCode('EUR')), 
		new Price(300, Currency::fromCode('EUR')),
	);
    expect($summary->finalPrice->toInt())->toBe(900);
    expect($summary->isFree())->toBeFalse();
    expect(PriceSummary::free(Currency::fromCode('EUR'))->isFree())->toBeTrue();

    $counts = StatusCounts::fromArray(['publish' => '2', 'draft' => 1]);
    expect($counts->toArray()['publish'])->toBe(2);
    expect($counts->toArray()['draft'])->toBe(1);

    $eventCounts = EventStatusCounts::fromArray(['publish' => 5, 'cancelled' => 2]);
    expect($eventCounts->toArray()['publish'])->toBe(5);
    expect($eventCounts->toArray()['cancelled'])->toBe(2);

    $bookingFormId = FormId::from(1);
    $attendeeFormId = FormId::from(2);
    if ($bookingFormId === null || $attendeeFormId === null) {
        throw new RuntimeException('Failed to create form ids in test.');
    }

    $forms = new EventForms($bookingFormId, $attendeeFormId);
    expect($forms->hasBookingForm())->toBeTrue();
    expect($forms->hasAttendeeForm())->toBeTrue();
    expect($forms->hasForms())->toBeTrue();
});

test('taxonomy collection can filter by taxonomy name', function () {
    $collection = new TaxonomyCollection(
        new Taxonomy(1, 'music', 'Music', 'event_category'),
        new Taxonomy(2, 'art', 'Art', 'event_category'),
        new Taxonomy(3, 'featured', 'Featured', 'event_tag')
    );

    $categories = $collection->forTaxonomy('event_category');
    $tags = $collection->forTaxonomy('event_tag');

    expect(count($categories))->toBe(2);
    expect(count($tags))->toBe(1);
    expect($tags[0]->slug)->toBe('featured');
});
