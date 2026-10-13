import NumberField, { type NumberFieldProps } from './NumberField';

type CurrencyFieldProps = NumberFieldProps & {
	currency: string;
};

const getCurrencySymbol = (currency: string): string => {
	try {
		return (
			new Intl.NumberFormat(undefined, {
				style: 'currency',
				currency,
				currencyDisplay: 'narrowSymbol',
			})
				.formatToParts(0)
				.find((part) => part.type === 'currency')?.value ?? currency
		);
	} catch {
		return currency;
	}
};

const CurrencyField = ({
	currency,
	step = 0.01,
	...props
}: CurrencyFieldProps) => (
	<NumberField {...props} step={step} suffix={getCurrencySymbol(currency)} />
);

export default CurrencyField;
