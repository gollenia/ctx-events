<?php

namespace Contexis\Events\Location\Application;

use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Location\Domain\LocationRepository;

final class GetLocation
{
	public function __construct(private readonly LocationRepository $locationRepository)
	{
	}

	public function execute(int $id): ?LocationDto
	{
		$location = $this->locationRepository->find(LocationId::from($id));
		if ($location === null) {
			return null;
		}

		return LocationDto::fromDomainModel($location);
	}
}