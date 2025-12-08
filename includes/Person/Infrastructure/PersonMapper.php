<?php
declare(strict_types=1);

namespace Contexis\Events\Person\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Wordpress\PostStatusMapper;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Person\Domain\Person;
use Contexis\Events\Person\Domain\PersonId;
use Contexis\Events\Shared\Domain\ValueObjects\Email;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;

final readonly class PersonMapper
{
    public static function map(PostSnapshot $snapshot): Person
    {

        return new Person(
            id: PersonId::from($snapshot->id),
            status: PostStatusMapper::fromPost($snapshot->post_status),
            givenName: $snapshot->getMetaValue(PersonMeta::FIRST_NAME),
            familyName: $snapshot->getMetaValue(PersonMeta::LAST_NAME),
            honorificSuffix: $snapshot->getMetaValue(PersonMeta::PREFIX),
            honorificPrefix: $snapshot->getMetaValue(PersonMeta::SUFFIX),
            email: Email::tryFrom($snapshot->getMetaValue(PersonMeta::EMAIL)),
            telephone: $snapshot->getMetaValue(PersonMeta::PHONE),
            sameAs: $snapshot->getMetaValue(PersonMeta::SAME_AS),
            jobTitle: $snapshot->getMetaValue(PersonMeta::POSITION),
            worksFor: $snapshot->getMetaValue(PersonMeta::ORGANIZATION),
            imageId: ImageId::from($snapshot->getThumbnailId())
        );
    }
}
