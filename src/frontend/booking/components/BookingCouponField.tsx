import { CouponField } from './CouponField';
import { useCouponPreflight } from '../hooks/useCouponPreflight';
import { calculateBookingTotal, calculateCouponDiscount } from '../pricing';
import type { BookingData, BookingState, PaymentStateUpdates } from '../types';

type Props = {
	data: BookingData;
	state: BookingState;
	postId: number;
	onPaymentStateChange: (updates: PaymentStateUpdates) => void;
	className?: string;
};

export function BookingCouponField({
	data,
	state,
	postId,
	onPaymentStateChange,
	className = '',
}: Props) {
	const {
		status: couponStatus,
		result: couponResult,
		message: couponMessage,
		check: checkCoupon,
		reset: resetCouponCheck,
	} = useCouponPreflight();

	if (!data.couponsEnabled) {
		return null;
	}

	const currency = data.tickets[0]?.price.currency ?? 'EUR';
	const totalPrice = calculateBookingTotal(data.tickets, state.attendees);
	const liveDiscountAmount = calculateCouponDiscount(
		totalPrice,
		state.couponCheckResult,
	);

	async function handleCouponCheck() {
		if (!state.couponCode.trim()) {
			resetCouponCheck();
			onPaymentStateChange({ couponCheckResult: null });
			return;
		}

		const result = await checkCoupon({
			code: state.couponCode.trim(),
			eventId: postId,
			bookingPrice: totalPrice,
			currency,
		});

		onPaymentStateChange({ couponCheckResult: result });
	}

	return (
		<div className={className}>
			<CouponField
				code={state.couponCode}
				currency={currency}
				couponStatus={couponStatus}
				couponResult={couponResult}
				appliedCoupon={state.couponCheckResult}
				couponMessage={couponMessage}
				liveDiscountAmount={liveDiscountAmount}
				onCodeChange={(nextCode) =>
					onPaymentStateChange({
						couponCode: nextCode,
						couponCheckResult: null,
					})
				}
				onCheck={handleCouponCheck}
			/>
		</div>
	);
}
