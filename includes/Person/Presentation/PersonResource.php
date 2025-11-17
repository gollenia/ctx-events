<?php 

namespace Contexis\Events\Person\Presentation;

use Contexis\Events\Pwerson\Application\PersonDto;

final class PersonResource
{
	public function __construct(
		private readonly PersonDto $personDto
	) {
	}

	public function toArray(): array
	{
		return [
			'id' => $this->personDto->id,
			'givenName' => $this->personDto->givenName,
			'familyName' => $this->personDto->familyName,
			'email' => $this->personDto->email,
			'telephone' => $this->personDto->telephone,
			'sameAs' => $this->personDto->sameAs,
		];
	}
}