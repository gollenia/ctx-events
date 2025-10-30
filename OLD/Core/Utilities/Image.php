<?php

namespace Contexis\Events\Core\Utilities;

class Image {

	public int $attachment_id = 0;
	public string $url = "";
	public array $sizes = [];
	public string $alt = "";
	public string $title = "";
	public string $mime_type = "";

	public static function from_post_id(int $post_id) : self {

		$thumbnail = get_post_thumbnail_id($post_id);

		if(!$thumbnail) return new self;

		$instance = new self;
		
		$instance->attachment_id = $thumbnail;
		$instance->url = wp_get_attachment_url($thumbnail);
		$instance->alt = get_post_meta($thumbnail, '_wp_attachment_image_alt', true);
		$instance->title = get_the_title($thumbnail);
		$instance->mime_type = get_post_mime_type($thumbnail);
		
		foreach(get_intermediate_image_sizes($thumbnail) as $size) {
			$instance->sizes[$size] = array_combine(['url', 'width',  'height', 'resized'], wp_get_attachment_image_src( $thumbnail, $size) );
		}
		
		return $instance;
	}

	public function url_for($size) {
		if(!isset($this->sizes[$size])) return $this->url;
		return $this->sizes[$size]['url'];
	}
}