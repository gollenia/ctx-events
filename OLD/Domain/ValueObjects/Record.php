<?php

namespace Contexis\Events\Domain\Models;

use DateTime;

class Note implements \JsonSerializable {
	public int $id = 0;
	public int $user_id = 0;
	public string $text = '';
	public DateTime $date;

	public static function from_array(array $data): Note {
		$instance = new self();
		$instance->id = $data['id'] ?? 0;
		$instance->user_id = $data['user_id'] ?? 0;
		$instance->text = $data['text'] ?? '';
		$instance->date = isset($data['date']) ? new DateTime($data['date']) : new DateTime();
		return $instance;
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'user_id' => $this->user_id,
			'text' => $this->text,
			'date' => $this->date->format(DATE_ATOM)
		];
	}
}