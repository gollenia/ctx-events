<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use Contexis\Events\Shared\Infrastructure\Wordpress\DuplicatePost;

final class CouponDuplicatePost extends DuplicatePost
{
    protected function supportsPostType(string $postType): bool
    {
        return $postType === CouponPost::POST_TYPE;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildPostData(\WP_Post $sourcePost): array
    {
        $data = parent::buildPostData($sourcePost);
        $data['post_status'] = $sourcePost->post_status;

        return $data;
    }
}
