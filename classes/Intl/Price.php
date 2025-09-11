<?php

namespace Contexis\Events\Intl;

use NumberFormatter;
use stdClass;

class Price {
	public float $price;
	public bool $free;
	public string $format;
	public string $currency;
	public float $donation;
	public float $discount;
	private NumberFormatter $number_formatter;
	
	public function __construct(float $price) {
		$this->price = floatval($price);

		$this->number_formatter = new NumberFormatter(
			get_locale(), 
			NumberFormatter::CURRENCY
		);

		$this->number_formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, get_option('dbem_bookings_currency'));

		$this->free = $this->is_free();
		$this->format = $this->get_format();
		$this->currency = get_option('dbem_bookings_currency') ?: 'EUR';
	}
	
	public function get_format() : string {
		return $this->number_formatter->format($this->price);
	}

	public function get_currency() : string {
		return $this->number_formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
	}

	public function get_currency_code() : string {
		return $this->number_formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE);
	}

	public function is_free() : bool {
		return $this->price === 0.0;
	}

	public static function format(float $price) : string {
		$priceObject = new Price($price);
		return $priceObject->format;
	}

	public static function currency_code() : string {
		return get_option('dbem_bookings_currency');
	}

	public static function currency_symbol($currency = null) : string {
		$formatter = new Price(0);
		if ($currency) {
			$formatter->number_formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currency);
		}
		return $formatter->get_currency();
	}
}