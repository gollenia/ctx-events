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
	
	/**
	 * Returns a Intl formatted date range or single date
	 *
	 * @param integer $start TimeStamp
	 * @param integer $end TimeStamp
	 * @return string
	 */
	public function get_format() {
		return $this->number_formatter->format($this->price);
	}

	public function get_currency() {
		return $this->number_formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
	}

	public function get_currency_code() {
		return $this->number_formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE);
	}

	public function is_free() {
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

	public static function currency_list() : stdClass {
		$currencies = new \stdClass();
		$currencies->names = array('EUR' => 'EUR - Euros','USD' => 'USD - U.S. Dollars','GBP' => 'GBP - British Pounds','CAD' => 'CAD - Canadian Dollars','AUD' => 'AUD - Australian Dollars','BRL' => 'BRL - Brazilian Reais','CZK' => 'CZK - Czech koruna','DKK' => 'DKK - Danish Kroner','HKD' => 'HKD - Hong Kong Dollars','HUF' => 'HUF - Hungarian Forints','ILS' => 'ILS - Israeli New Shekels','JPY' => 'JPY - Japanese Yen','MYR' => 'MYR - Malaysian Ringgit','MXN' => 'MXN - Mexican Pesos','TWD' => 'TWD - New Taiwan Dollars','NZD' => 'NZD - New Zealand Dollars','NOK' => 'NOK - Norwegian Kroner','PHP' => 'PHP - Philippine Pesos','PLN' => 'PLN - Polish Zlotys','SGD' => 'SGD - Singapore Dollars','SEK' => 'SEK - Swedish Kronor','CHF' => 'CHF - Swiss Francs','THB' => 'THB - Thai Baht','TRY' => 'TRY - Turkish Liras', 'RUB'=>'RUB - Russian Ruble');
		$currencies->symbols = array( 'EUR' => '&euro;','USD' => '$','GBP' => '&pound;','CAD' => '$','AUD' => '$','BRL' => 'R$','CZK' => 'K&#269;','DKK' => 'kr','HKD' => '$','HUF' => 'Ft','JPY' => '&#165;','MYR' => 'RM','MXN' => '$','TWD' => '$','NZD' => '$','NOK' => 'kr','PHP' => 'Php', 'PLN' => '&#122;&#322;','SGD' => '$','SEK' => 'kr','CHF' => 'CHF','TRY' => 'TL','RUB'=>'&#8381;');
		$currencies->true_symbols = array( 'EUR' => '€','USD' => '$','GBP' => '£','CAD' => '$','AUD' => '$','BRL' => 'R$','CZK' => 'Kč','DKK' => 'kr','HKD' => '$','HUF' => 'Ft','JPY' => '¥','MYR' => 'RM','MXN' => '$','TWD' => '$','NZD' => '$','NOK' => 'kr','PHP' => 'Php','PLN' => 'zł','SGD' => '$','SEK' => 'kr','CHF' => 'CHF','TRY' => 'TL', 'RUB'=>'₽');
		return $currencies;
	}
}