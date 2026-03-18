<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Application;

use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Application\ValueObjects\UserContext;

final class GetPerson
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    public function execute(int $id, PersonIncludes $includes, UserContext $context ): ?PersonDto
    {
        $person = $this->personRepository->find(PersonId::from($id));

		if($person === null) {
			return null;
		}

        return PersonDto::fromDomainModel($person);
    }
}
