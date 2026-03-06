import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { PriceSummary } from '../components/PriceSummary';
import type { AttendeePayload, BookingData, BookingState, SubmitResult } from '../types';

type Props = {
	data: BookingData;
	bookingState: BookingState;
	onResult: (result: SubmitResult) => void;
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
	onSubmit,
	postId,
	isSubmitting,
}: Props) {
	const [gateway, setGateway] = useState<string>(
		bookingState.gateway || (data.gateways[0]?.id ?? ''),
	);
	const [couponCode, setCouponCode] = useState<string>(bookingState.couponCode);
	const [consent, setConsent] = useState(false);
	const [consentError, setConsentError] = useState('');
	const [submitError, setSubmitError] = useState('');

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
			coupon_code: couponCode || undefined,
		});

		if (result.type === 'error') {
			setSubmitError(result.message);
			return;
		}

		if (result.type === 'mollie') {
			window.location.replace(result.url);
			return;
		}

		onResult(result);
	}

	return (
		<div className="booking-section booking-section--payment">
			<PriceSummary tickets={data.tickets} ticketCounts={bookingState.tickets} />

			{data.gateways.length > 1 && (
				<fieldset className="booking-gateway-select">
					<legend className="booking-gateway-select__legend">
						{__('Payment method', 'ctx-events')}
					</legend>
					{data.gateways.map((gw) => (
						<label key={gw.id} className="booking-gateway-select__option">
							<input
								type="radio"
								name="gateway"
								value={gw.id}
								checked={gateway === gw.id}
								onChange={() => setGateway(gw.id)}
							/>
							{gw.title}
						</label>
					))}
				</fieldset>
			)}

			{data.couponsEnabled && (
				<div className="booking-coupon">
					<label className="booking-coupon__label" htmlFor="booking-coupon-code">
						{__('Coupon code', 'ctx-events')}
					</label>
					<input
						id="booking-coupon-code"
						type="text"
						className="booking-coupon__input"
						value={couponCode}
						onChange={(e) => setCouponCode(e.target.value)}
						placeholder={__('Optional', 'ctx-events')}
					/>
				</div>
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
							__('I agree to the <a href="/privacy" target="_blank">privacy policy</a>.', 'ctx-events'),
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
					{isSubmitting ? __('Processing…', 'ctx-events') : __('Book now', 'ctx-events')}
				</button>
			</div>
		</div>
	);
}
