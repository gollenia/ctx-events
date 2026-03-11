import type { Price } from 'src/types/types';
import getLocale from './locale';

/**
 * Formats a price safely.
 * @param {Price} price - The price object
 */
const formatPrice = (price: Price): string => {
	const numberValue = Number(price.amountCents) / 100;
	const currency = price.currency;

	if (Number.isNaN(numberValue)) {
		console.warn('formatPrice: Value is not a number', price.amountCents);
		return '';
	}

	try {
		return new Intl.NumberFormat(getLocale(), {
			style: 'currency',
			currency: currency,
		}).format(numberValue);
	} catch (e) {
		return `${numberValue} ${currency}`;
	}
};

const fromatPriceRange: (min: Price, max: Price, currency?: string) => string =
	(min: Price, max: Price): string => {
		const minValue = Number(min.amountCents) / 100;
		const maxValue = Number(max.amountCents) / 100;
		if (Number.isNaN(minValue) || Number.isNaN(maxValue)) {
			console.warn(
				'formatPriceRange: Min or Max value is not a number',
				min,
				max,
			);
			return '';
		}

		try {
			const formattedMin = new Intl.NumberFormat(getLocale(), {
				style: 'currency',
				currency: min.currency,
			}).format(minValue);

			const formattedMax = new Intl.NumberFormat(getLocale(), {
				style: 'currency',
				currency: max.currency,
			}).format(maxValue);

			return `${formattedMin} - ${formattedMax}`;
		} catch (e) {
			return `${minValue} - ${maxValue} ${min.currency}`;
		}
	};

export { formatPrice, fromatPriceRange };
