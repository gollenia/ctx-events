import { Flex, Input, Range } from '@contexis/wp-react-form';

const MAX_SLIDER_AMOUNT = 500;
const MID_SLIDER_AMOUNT = 100;
const SLIDER_MAX = 100;

const clamp = (value: number, min: number, max: number): number =>
	Math.min(Math.max(value, min), max);

const getSliderExponent = (minimumAmount: number): number => {
	if (minimumAmount >= MID_SLIDER_AMOUNT || minimumAmount >= MAX_SLIDER_AMOUNT) {
		return 1;
	}

	return (
		Math.log(
			(MID_SLIDER_AMOUNT - minimumAmount) / (MAX_SLIDER_AMOUNT - minimumAmount),
		) / Math.log(0.5)
	);
};

const sliderPositionToAmount = (
	position: number,
	minimumAmount: number,
): number => {
	if (minimumAmount >= MAX_SLIDER_AMOUNT) {
		return Math.round(minimumAmount);
	}

	const exponent = getSliderExponent(minimumAmount);
	const progress = clamp(position, 0, SLIDER_MAX) / SLIDER_MAX;
	const amount =
		minimumAmount +
		(MAX_SLIDER_AMOUNT - minimumAmount) * progress ** exponent;

	return Math.round(clamp(amount, minimumAmount, MAX_SLIDER_AMOUNT));
};

const amountToSliderPosition = (
	amount: number,
	minimumAmount: number,
): number => {
	if (minimumAmount >= MAX_SLIDER_AMOUNT) {
		return 0;
	}

	const exponent = getSliderExponent(minimumAmount);
	const clampedAmount = clamp(amount, minimumAmount, MAX_SLIDER_AMOUNT);
	const progress =
		(clampedAmount - minimumAmount) / (MAX_SLIDER_AMOUNT - minimumAmount);

	return Math.round(progress ** (1 / exponent) * SLIDER_MAX);
};

type Props = {
	amount: string;
	currency: string;
	minimumAmount?: number;
	onChange: (value: string) => void;
};

export function DonationAmountSelector({
	amount,
	currency,
	minimumAmount = 0,
	onChange,
}: Props) {
	const numericAmount = Number(amount.replace(',', '.'));
	const sliderValue = Number.isNaN(numericAmount)
		? 0
		: amountToSliderPosition(numericAmount, minimumAmount);

	return (
		<Flex className="booking-donation-amount" gap="1rem" align="center">
			<Input
				name="donation-amount"
				onChange={(value) => onChange(String(value))}
				placeholder="0,00"
				type="number"
				value={amount}
				unit={currency}
			/>

			<Range
				disabled={false}
				hasLabels={false}
				hasTicks={false}
				max={SLIDER_MAX}
				min={0}
				name="donation-amount-range"
				onChange={(value) =>
					onChange(
						String(
							sliderPositionToAmount(Number(value), minimumAmount),
						),
					)
				}
				placeholder=""
				required={false}
				showValue={false}
				type="range"
				value={String(sliderValue)}
				width={6}
			/>
		</Flex>
	);
}
