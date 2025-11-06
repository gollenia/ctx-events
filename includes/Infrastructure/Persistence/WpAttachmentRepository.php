<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Domain\Repositories\AttachmentRepository;
use Contexis\Events\Domain\ValueObjects\Id\AttachmentId;
use Contexis\Events\Domain\ValueObjects\Media;
use Contexis\Events\Domain\ValueObjects\ImageSizes;
use Contexis\Events\Domain\ValueObjects\Attachment;

final class WpAttachmentRepository implements AttachmentRepository
{
    public function find(?AttachmentId $id): ?Attachment
    {
        $metadata = wp_get_attachment_metadata($id, true);

        return new \Contexis\Events\Domain\ValueObjects\Attachment(
            url: wp_get_attachment_url($id),
            alt_text: $metadata['_wp_attachment_image_alt'] ?? '',
            width: $metadata['width'] ?? 0,
            height: $metadata['height'] ?? 0,
            mimeType: $metadata['mime_type'] ?? '',
            sizes: new ImageSizes(
                thumbnail: wp_get_attachment_image_src($id, 'thumbnail')[0] ?? '',
                medium: wp_get_attachment_image_src($id, 'medium')[0] ?? '',
                large: wp_get_attachment_image_src($id, 'large')[0] ?? '',
                original: wp_get_attachment_image_src($id, 'original')[0] ?? ''
            )
        );
    }
}
