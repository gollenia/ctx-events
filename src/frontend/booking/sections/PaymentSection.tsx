import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { CouponField } from '../components/CouponField';
import { PaymentGatewaySelect } from '../components/PaymentGatewaySelect';
import { PriceSummary } from '../components/PriceSummary';
import { useCouponPreflight } from '../hooks/useCouponPreflight';
import { calculateBookingTotal, calculateCouponDiscount } from '../pricing';
import type {
	AttendeePayload,
	BookingData,
	BookingState,
	PaymentStateUpdates,
	SubmitResult,
} from '../types';

type Props = {
	data: BookingData;
	bookingState: BookingState;
	onResult: (result: SubmitResult) => void;
	onPaymentStateChange: (updates: PaymentStateUpdates) => void;
	onSubmit: (payload: {
		token: string;
		event_id: number;
		registration: Record<string, unknown>;
		attendees: AttendeePayload[];
		gateway: string;
		coupon_code?: string;
	}) => Promise<SubmitResult>;
	postId: number;
	isSubmitting: boolean;
};

export function PaymentSection({
	data,
	bookingState,
	onResult,
	onPaymentStateChange,
	onSubmit,
	postId,
	isSubmitting,
}: Props) {
	const [consent, setConsent] = useState(false);
	const [consentError, setConsentError] = useState('');
	const [submitError, setSubmitError] = useState('');
	const {
		status: couponStatus,
		result: couponResult,
		message: couponMessage,
		check: checkCoupon,
		reset: resetCouponCheck,
	} = useCouponPreflight();
	const gateway = bookingState.gateway || (data.gateways[0]?.id ?? '');
	const couponCode = bookingState.couponCode;
	const appliedCoupon = bookingState.couponCheckResult;
	const totalPrice = calculateBookingTotal(data.tickets, bookingState.tickets);
	const currency = data.tickets[0]?.currency ?? 'EUR';
	const liveDiscountAmount = calculateCouponDiscount(totalPrice, appliedCoupon);

	async function handleCouponCheck() {
		if (!couponCode.trim()) {
			resetCouponCheck();
			onPaymentStateChange({ couponCheckResult: null });
			return;
		}

		const result = await checkCoupon({
			code: couponCode.trim(),
			eventId: postId,
			bookingPrice: totalPrice,
			currency,
		});

		onPaymentStateChange({ couponCheckResult: result });
	}

	async function handleSubmit() {
		if (!consent) {
			setConsentError(__('Please accept the terms to continue.', 'ctx-events'));
			return;
		}
		setConsentError('');
		setSubmitError('');

		const result = await onSubmit({
			token: data.token,
			event_id: postId,
			registration: bookingState.registration,
			attendees: bookingState.attendees,
			gateway,
			coupon_code: couponCode.trim() || undefined,
		});

		if (result.type === 'error') {
			setSubmitError(result.message);
			return;
		}

		onResult(result);
	}

	return (
		<div className="booking-section booking-section--payment">
			<PriceSummary
				tickets={data.tickets}
				ticketCounts={bookingState.tickets}
				coupon={appliedCoupon}
			/>

			<PaymentGatewaySelect
				gateways={data.gateways}
				selectedGateway={gateway}
				onChange={(nextGateway) =>
					onPaymentStateChange({ gateway: nextGateway })
				}
			/>

			{data.couponsEnabled && (
				<CouponField
					code={couponCode}
					currency={currency}
					couponStatus={couponStatus}
					couponResult={couponResult}
					appliedCoupon={appliedCoupon}
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
			)}

			<label className="booking-consent">
				<input
					type="checkbox"
					className="booking-consent__checkbox"
					checked={consent}
					onChange={(e) => setConsent(e.target.checked)}
				/>
				<span
					className="booking-consent__label"
					// eslint-disable-next-line react/no-danger
					dangerouslySetInnerHTML={{
						__html: sprintf(
							// translators: %s is linked text
							__(
								'I agree to the <a href="/privacy" target="_blank">privacy policy</a>.',
								'ctx-events',
							),
						),
					}}
				/>
			</label>

			{consentError && (
				<p className="booking-error" role="alert">
					{consentError}
				</p>
			)}

			{submitError && (
				<p className="booking-error" role="alert">
					{submitError}
				</p>
			)}

			<div className="booking-section__footer">
				<button
					type="button"
					className="booking-btn booking-btn--primary"
					onClick={handleSubmit}
					disabled={isSubmitting}
				>
					{isSubmitting
						? __('Processing…', 'ctx-events')
						: __('Book now', 'ctx-events')}
				</button>
			</div>
		</div>
	);
}
