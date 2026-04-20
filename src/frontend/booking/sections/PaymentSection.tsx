import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Checkbox } from '@contexis/wp-react-form';
import { DonationAdvertisement } from '../components/DonationAdvertisement';
import { PaymentGatewaySelect } from '../components/PaymentGatewaySelect';
import { SectionFooter } from '../components/SectionFooter';
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
		donation_amount?: number;
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
	const gateway = bookingState.gateway || (data.gateways[0]?.id ?? '');
	const donationAmount = Math.max(0, bookingState.donationAmount || 0);
	const currency = data.tickets[0]?.price.currency ?? 'EUR';

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
			coupon_code: bookingState.couponCode.trim() || undefined,
			donation_amount: donationAmount > 0 ? donationAmount : undefined,
		});

		if (result.type === 'error') {
			setSubmitError(result.message);
			return;
		}

		onResult(result);
	}

	return (
		<div
			className="booking-section booking-section--payment"
			data-testid="booking-section-payment"
		>
			<PaymentGatewaySelect
				gateways={data.gateways}
				selectedGateway={gateway}
				onChange={(nextGateway) =>
					onPaymentStateChange({ gateway: nextGateway })
				}
			/>

			{data.donationEnabled && data.donationAdvertisement && (
				<DonationAdvertisement
					content={data.donationAdvertisement}
					currency={currency}
					donationAmount={donationAmount}
					onChange={(nextDonationAmount) =>
						onPaymentStateChange({ donationAmount: nextDonationAmount })
					}
				/>
			)}

			<div className="booking-consent">
				<Checkbox
					name="privacy-consent"
					width={6}
					value={consent}
					error={consentError || undefined}
					label={__(
						'I agree to the <a href="/privacy" target="_blank">privacy policy</a>.',
						'ctx-events',
					)}
					onChange={(value) => {
						setConsent(Boolean(value));
						setConsentError('');
					}}
				/>
			</div>

			{submitError && (
				<p className="booking-error" role="alert">
					{submitError}
				</p>
			)}

			<SectionFooter>
				<Button
					onClick={handleSubmit}
					disabled={isSubmitting}
					data-testid="booking-submit"
				>
					{isSubmitting
						? __('Processing…', 'ctx-events')
						: __('Book now', 'ctx-events')}
				</Button>
			</SectionFooter>
		</div>
	);
}
