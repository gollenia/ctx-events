<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Location;

final class LocationCollection extends AbstractTypedCollection {
	public function __construct(Location ...$locations)
	{
		$this->items = $locations;
	}

}