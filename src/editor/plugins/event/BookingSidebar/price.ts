import { formatPrice } from '@events/i18n';

export const parsePriceInputToCents = (value: string): number => {
	const normalized = value.trim().replace(/\s/g, '').replace(',', '.');
	if (normalized === '') {
		return 0;
	}

	const amount = Number(normalized);

	return Number.isFinite(amount) ? Math.round(amount * 100) : 0;
};

export const formatCentsAsPriceInput = (value: string | number): string => {
	const amountCents = Number(value || 0);
	if (!Number.isFinite(amountCents)) {
		return '';
	}

	return String(amountCents / 100);
};

export const formatCentsAsPrice = (
	value: string | number,
	currency: string,
): string => {
	const amountCents = Number(value || 0);

	return formatPrice({
		amountCents: Number.isFinite(amountCents) ? amountCents : 0,
		currency,
	});
};
