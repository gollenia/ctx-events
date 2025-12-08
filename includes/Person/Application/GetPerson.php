<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Application;

use Contexis\Events\Person\Domain\PersonRepository;
use Contexis\Events\Person\Application\PersonDto;
use Contexis\Events\Person\Domain\PersonId;

final class GetPerson
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    public function execute(int $id): PersonDto
    {
        $person = $this->personRepository->find(PersonId::from($id));

        return PersonDto::fromDomainModel($person);
    }
}
