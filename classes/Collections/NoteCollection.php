<?php

namespace Contexis\Events\Collections;

use Contexis\Events\Models\Note;

use IteratorAggregate;
use Countable;
use JsonSerializable;

class NoteCollection implements IteratorAggregate, Countable, JsonSerializable {
	
	private array $items = [];

	public static function from_array(array $notes): NoteCollection {
		$instance = new self();
		foreach ($notes as $note) {
			$instance->items[] = Note::from_array($note);
		}
		return $instance;
	}

	public function add(string $text): ?Note {
		if(trim($text) === '') {
			return null;
		}
		$note = new Note();
		$note->id = $this->get_next_id();
		$note->user_id = get_current_user_id();
		$note->text = $text;
		$note->date = new \DateTime();
		$this->items[] = $note;
		return $note;
	}

	public function remove(Note $item): void {
		$this->items = array_filter(
			$this->items,
			fn($i) => $i !== $item
		);
		
		$this->items = array_values($this->items);
	}

	private function get_next_id(): int {
		$max = 0;
		foreach ($this->items as $item) {
			if ($item->id > $max) {
				$max = $item->id;
			}
		}
		return $max + 1;
	}

	public function get_by_id(int $id): ?Note {
		foreach ($this->items as $item) {
			if ($item->id === $id) {
				return $item;
			}
		}
		return null;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->items);
	}

	public function count(): int {
		return count($this->items);
	}

	public function jsonSerialize(): array {
		return array_map(fn($note) => $note->jsonSerialize(), $this->items);
	}
}