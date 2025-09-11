<?php

namespace Contexis\Events\Models;

use Contexis\Events\Core\Utilities\Image;

class Speaker implements \JsonSerializable{

	var int $id = 0;
	var $name = "";
	var $email = "";
	var $gender = "";
	var $phone = "";
	var ?Image $image = null;
	var $slug = "";
	var $role = "";
	var $description = "";

	public function __construct(int $id = 0)
	{
		if( !$id ) return;

		$this->id = $id;
		self::get($this);
	}

	public static function get($speaker) {

		if(!$speaker) return new Speaker();

		if(is_int($speaker)) {
			return new Speaker($speaker);
		}

		$args = array(
			'p'         => $speaker->id, // ID of a page, post, or custom type
			'post_type' => 'event-speaker'
		);

		$query = new \WP_Query($args);
		$result = $query->get_posts();

		if(empty($result)) return $speaker;

		$data = $result[0];
		$speaker->image = Image::from_post_id($speaker->id);
		$speaker->name = $data->post_title;
		$speaker->email = get_post_meta($speaker->id,'_email', true);
		$speaker->phone = get_post_meta($speaker->id,'_phone', true);
		$speaker->role = get_post_meta($speaker->id,'_role', true);
		$speaker->gender = get_post_meta($speaker->id,'_gender', true);
		$speaker->slug = $data->post_name;
		return $speaker;
	}

	

	public function jsonSerialize() : array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'email' => $this->email,
			'phone' => $this->phone,
			'role' => $this->role,
			'image' => $this->image,
		];
	}
}