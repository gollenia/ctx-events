import { BookingCouponField } from './BookingCouponField';
import { BookingSummaryPanel } from './BookingSummaryPanel';
import type { BookingData, BookingState, PaymentStateUpdates } from '../types';

type Props = {
	data: BookingData;
	state: BookingState;
	postId: number;
	onPaymentStateChange: (updates: PaymentStateUpdates) => void;
	className?: string;
	visible?: boolean;
	couponClassName?: string;
};

export function BookingSummarySlot({
	data,
	state,
	postId,
	onPaymentStateChange,
	className = '',
	visible = true,
	couponClassName = 'booking-coupon-slot',
}: Props) {
	return (
		<BookingSummaryPanel
			data={data}
			state={state}
			visible={visible}
			className={className}
			couponField={
				<BookingCouponField
					data={data}
					state={state}
					postId={postId}
					onPaymentStateChange={onPaymentStateChange}
					className={couponClassName}
				/>
			}
			showCouponField
		/>
	);
}
