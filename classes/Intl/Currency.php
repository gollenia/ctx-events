<?php

namespace Contexis\Events\Intl;

readonly class Currency {

	public function __construct(
		public string $code,
		public string $name,
		public string $symbol,
		public string $utf_symbol
	) {}

	public static function get(string $code): ?Currency {
		foreach (self::currency_list() as $currency) {
			if ($currency->code === $code) {
				return $currency;
			}
		}
		return null;
	}

	public static function names(): array {
		return array_column(self::currency_list(), 'name', 'code');
	}

	public static function currency_list(): array {
		return [
			new Currency('EUR', 'EUR - Euros', '&euro;', '€'),
			new Currency('USD', 'USD - U.S. Dollars', '$', '$'),
			new Currency('GBP', 'GBP - British Pounds', '&pound;', '£'),
			new Currency('CAD', 'CAD - Canadian Dollars', '$', '$'),
			new Currency('AUD', 'AUD - Australian Dollars', '$', '$'),
			new Currency('BRL', 'BRL - Brazilian Reais', 'R$', 'R$'),
			new Currency('CZK', 'CZK - Czech koruna', 'K&#269;', 'Kč'),
			new Currency('DKK', 'DKK - Danish Kroner', 'kr', 'kr'),
			new Currency('HKD', 'HKD - Hong Kong Dollars', '$', '$'),
			new Currency('HUF', 'HUF - Hungarian Forints', 'Ft', 'Ft'),
			new Currency('ILS', 'ILS - Israeli New Shekels', '₪', '₪'),
			new Currency('JPY', 'JPY - Japanese Yen', '&#165;', '¥'),
			new Currency('MYR', 'MYR - Malaysian Ringgit', 'RM', 'RM'),
			new Currency('MXN', 'MXN - Mexican Pesos', '$', '$'),
			new Currency('TWD', 'TWD - New Taiwan Dollars', '$', '$'),
			new Currency('NZD', 'NZD - New Zealand Dollars', '$', '$'),
			new Currency('NOK', 'NOK - Norwegian Kroner', 'kr', 'kr'),
			new Currency('PHP', 'PHP - Philippine Pesos', 'Php', 'Php'),
			new Currency('PLN', 'PLN - Polish Zlotys', '&#122;&#322;', 'zł'),
			new Currency('SGD', 'SGD - Singapore Dollars', '$', '$'),
			new Currency('SEK', 'SEK - Swedish Kronor', 'kr', 'kr'),
			new Currency('CHF', 'CHF - Swiss Francs', 'CHF', 'CHF'),
			new Currency('THB', 'THB - Thai Baht', '฿', '฿'),
			new Currency('TRY', 'TRY - Turkish Liras', 'TL', 'TL'),
			new Currency('RUB', 'RUB - Russian Ruble', '&#8381;', '₽')
		];
	}
}