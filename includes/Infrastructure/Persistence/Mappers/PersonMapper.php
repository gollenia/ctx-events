<?php

namespace Contexis\Events\Infrastructure\Persistence\Mappers;

use Contexis\Events\Domain\ValueObjects\Email;
use Contexis\Events\Domain\Models\Person;
use Contexis\Events\Domain\ValueObjects\Id\ImageId;
use Contexis\Events\Infrastructure\PostTypes\PostSnapshot;

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
            website: $snapshot->getMetaValue('website'),
            jobTitle: $snapshot->getMetaValue('job_title'),
            worksFor: $snapshot->getMetaValue('works_for'),
            attachment_id: ImageId::from($snapshot->getThumbnailId())
        );
    }
}
