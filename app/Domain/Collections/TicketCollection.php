<?php

namespace Contexis\Events\Domain\Collections;

use Contexis\Events\Domain\Models\Ticket;
use IteratorAggregate;
use Countable;

final class TicketCollection implements IteratorAggregate, Countable {

	/** @var Ticket[] */
    private array $tickets;

	public function __construct(Ticket ...$tickets) {
		$this->tickets = $tickets;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->tickets);
	}

	public function count(): int {
		return count($this->tickets);
	}

	public function get_lowest_price(): ?int {
		$lowest_price = null;
		foreach ($this->tickets as $ticket) {
			$price = $ticket->price->amount_cents;
			$lowest_price = $lowest_price === null ? $price : min($lowest_price, $price);
		}
		return $lowest_price;
	}


	public function get_valid_tickets(): self {
		$valid_tickets = array_filter($this->tickets, function (Ticket $ticket) {
			return $ticket->enabled === true;
		});
		return new self(...$valid_tickets);
	}
}