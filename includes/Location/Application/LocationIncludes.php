<?php 

namespace Contexis\Events\Location\Application;

final class LocationIncludes
{
	public function __construct(
		public readonly bool $image = false
	) {
	}

	public static function fromArray(array $data): self
	{
		return new self(
			image: in_array('image', $data, true)
		);
	}
}