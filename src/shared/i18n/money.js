
import { getLocale } from './locale.js';

/**
 * Formats a price safely.
 * @param {number|string} value - The amount (e.g. 10.50 or "10.50")
 * @param {string} currency - The currency code (EUR, USD)
 */
const formatPrice = (value, currency = 'EUR') => {
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

export { formatPrice };