<?php

namespace Contexis\Events\Application\DTO;


class ImageDto {
	public function __construct(
		public readonly int $id,
		public readonly string $thumb_url,
		public readonly string $medium_url,
		public readonly string $full_url,
		public readonly string $alt_text,
	) {}
}