<?php

namespace Contexis\Events\Interfaces;

interface PostType {
	public const POST_TYPE = '';

    public static function init(): self;
	public static function get_slug(): string;
    public function register_post_type(): void;
    public function register_meta(): void;
}