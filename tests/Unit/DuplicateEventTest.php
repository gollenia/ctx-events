<?php

declare(strict_types=1);

use Contexis\Events\Event\Application\UseCases\DuplicateEvent;
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;

test('duplicate event delegates every selected date to the event repository', function () {
	$sourceEventId = EventId::from(123);
	$firstDate = new DateTimeImmutable('2026-09-10');
	$secondDate = new DateTimeImmutable('2026-09-17');
	$firstDuplicatedEventId = EventId::from(456);
	$secondDuplicatedEventId = EventId::from(789);
	$eventRepository = Mockery::mock(EventRepository::class);
	$eventRepository
		->shouldReceive('duplicateAt')
		->once()
		->with($sourceEventId, $firstDate)
		->andReturn($firstDuplicatedEventId);
	$eventRepository
		->shouldReceive('duplicateAt')
		->once()
		->with($sourceEventId, $secondDate)
		->andReturn($secondDuplicatedEventId);

	$useCase = new DuplicateEvent($eventRepository);

	expect($useCase->execute($sourceEventId, $firstDate, $secondDate))
		->toBe([$firstDuplicatedEventId, $secondDuplicatedEventId]);
});

test('duplicate event skips dates that cannot be duplicated', function () {
	$eventRepository = Mockery::mock(EventRepository::class);
	$eventRepository->shouldReceive('duplicateAt')->once()->andReturnNull();

	$useCase = new DuplicateEvent($eventRepository);

	expect($useCase->execute(EventId::from(999), new DateTimeImmutable('2026-09-10')))->toBe([]);
});
