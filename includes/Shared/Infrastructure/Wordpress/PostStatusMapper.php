<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Domain\ValueObjects\Status;

final class PostStatusMapper
{
    public static function fromPost(string $wpStatus): Status
    {
        return match ($wpStatus) {
            'publish' => Status::Published,
            'private' => Status::Private,
            'trash'   => Status::Trash,
            'draft'   => Status::Draft,
            default   => Status::Draft,
        };
    }

    public static function toPost(Status $status): string
    {
        return match ($status) {
            Status::Published      => 'publish',
            Status::Private        => 'private',
            Status::Trash        => 'trash',
            Status::Draft          => 'draft',
        };
    }
}
