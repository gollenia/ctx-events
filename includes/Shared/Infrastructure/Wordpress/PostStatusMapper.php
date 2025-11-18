<?php

namespace Contexis\Events\Infrastructure\Wordpress;

use Contexis\Events\Shared\Domain\ContentStatus;

final class PostStatusMapper
{
    public static function fromPost(string $wpStatus): ContentStatus
    {
        return match ($wpStatus) {
            'publish' => ContentStatus::Published,
            'private' => ContentStatus::Private,
            'trash'   => ContentStatus::Deleted,
            'pending' => ContentStatus::PendingReview,
            'draft'   => ContentStatus::Draft,
            default   => ContentStatus::Draft,
        };
    }

    public static function toPost(ContentStatus $status): string
    {
        return match ($status) {
            ContentStatus::Published      => 'publish',
            ContentStatus::Private        => 'private',
            ContentStatus::Deleted        => 'trash',
            ContentStatus::PendingReview  => 'pending',
            ContentStatus::Draft          => 'draft',
        };
    }
}
