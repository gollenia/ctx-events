<?php
declare(strict_types=1); 

namespace Contexis\Events\Location\Application;

use Contexis\Events\Location\Domain\LocationCriteria;
use Contexis\Events\Location\Domain\LocationRepository;

final class ListLocations
{
	public function __construct(
		private readonly LocationRepository $locationRepository
	) {
	}

	public function execute(LocationCriteria $criteria): array
	{
		$locations = $this->locationRepository->search($criteria);

		return array_map(
			fn ($location) => LocationDto::fromDomainModel($location),
			$locations->toArray()
		);
	}
}