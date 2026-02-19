
import getLocale from './locale';

/**
 * Formats a price safely.
 * @param {number|string} value - The amount (e.g. 10.50 or "10.50")
 * @param {string} currency - The currency code (EUR, USD)
 */
const formatPrice = (value: number | string, currency: string = 'EUR'): string => {
    const numberValue = Number(value);
    
    if (isNaN(numberValue)) {
        console.warn('formatPrice: Value is not a number', value);
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

const fromatPriceRange: (min: number | string, max: number | string, currency?: string) => string = (min: number | string, max: number | string, currency: string = 'EUR'): string => {
	const minValue = Number(min);
	const maxValue = Number(max);
	if (isNaN(minValue) || isNaN(maxValue)) {
		console.warn('formatPriceRange: Min or Max value is not a number', min, max);
		return '';
	}

	try {
		const formattedMin = new Intl.NumberFormat(getLocale(), {
			style: 'currency',
			currency: currency,
		}).format(minValue);

		const formattedMax = new Intl.NumberFormat(getLocale(), {
			style: 'currency',
			currency: currency,
		}).format(maxValue);

		return `${formattedMin} - ${formattedMax}`;
	} catch (e) {
		return `${minValue} - ${maxValue} ${currency}`;
	}
};

export { formatPrice, fromatPriceRange };