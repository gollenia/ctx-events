<?php

namespace Contexis\Events\Person\Infrastructure;

use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

final readonly class PersonMapper
{
    public static function map(PostSnapshot $snapshot): Person
    {
        return new Person(
            id: $snapshot->id,
            givenName: $snapshot->getMetaValue('first_name'),
            familyName: $snapshot->getMetaValue('last_name'),
            honorificSuffix: $snapshot->getMetaValue('honorific_suffix'),
            honorificPrefix: $snapshot->getMetaValue('honorific_prefix'),
            email: new Email($snapshot->getMetaValue('email')),
            telephone: $snapshot->getMetaValue('phone'),
            sameAs: $snapshot->getMetaValue('same_as'),
            jobTitle: $snapshot->getMetaValue('job_title'),
            worksFor: $snapshot->getMetaValue('works_for'),
            imageId: ImageId::from($snapshot->getThumbnailId())
        );
    }
}
