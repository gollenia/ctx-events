<?php
declare(strict_types=1);

namespace Contexis\Events\Location\Application;

use Contexis\Events\Location\Domain\LocationId;
use Contexis\Events\Location\Domain\LocationRepository;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;

final class GetLocation
{
	public function __construct(private readonly LocationRepository $locationRepository)
	{
	}

	public function execute(int $id, LocationIncludes $includes, UserContext $context ): ?LocationDto
	{
		$location = $this->locationRepository->find(LocationId::from($id));
		if ($location === null) {
			return null;
		}

		return LocationDto::fromDomainModel($location);
	}
}