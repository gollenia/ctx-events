import { formatPrice } from '@events/i18n';
import { __, sprintf } from '@wordpress/i18n';
import { Button } from '@contexis/wp-react-form';
import { BookingCard } from '../components/BookingCard';
import { usePaymentQr } from '../hooks/usePaymentQr';
import type { BookingPayment, PaymentReturnStatus } from '../types';

type Props = {
	reference: string;
	eventName: string;
	payment: BookingPayment | null;
	customerEmailStatus: 'sent' | 'failed' | 'skipped' | 'unknown';
	paymentStatus: PaymentReturnStatus | null;
	onClose: () => void;
};

function buildPaymentLink(reference: string): string {
	const url = new URL(window.location.href);
	url.searchParams.delete('ctx_events_booking_reference');
	url.searchParams.delete('ctx_events_payment_status');
	url.searchParams.set('ctx_events_booking_payment', reference);

	return url.toString();
}

function getReturnCopy(paymentStatus: PaymentReturnStatus | null) {
	switch (paymentStatus) {
		case 'paid':
			return {
				eyebrow: __('Payment received', 'ctx-events'),
				title: __('Your payment was successful', 'ctx-events'),
				message: __('We have received your payment and your booking is confirmed.', 'ctx-events'),
				showRetry: false,
			};
		case 'pending':
			return {
				eyebrow: __('Payment pending', 'ctx-events'),
				title: __('Your payment is still pending', 'ctx-events'),
				message: __('You can continue the payment process using the link below.', 'ctx-events'),
				showRetry: true,
			};
		case 'failed':
			return {
				eyebrow: __('Payment failed', 'ctx-events'),
				title: __('Your payment could not be completed', 'ctx-events'),
				message: __('You can try again using the payment link below.', 'ctx-events'),
				showRetry: true,
			};
		case 'expired':
			return {
				eyebrow: __('Payment expired', 'ctx-events'),
				title: __('Your payment link has expired', 'ctx-events'),
				message: __('You can generate a fresh payment and continue using the link below.', 'ctx-events'),
				showRetry: true,
			};
		case 'canceled':
			return {
				eyebrow: __('Payment canceled', 'ctx-events'),
				title: __('Your payment was canceled', 'ctx-events'),
				message: __('You can start the payment again using the link below.', 'ctx-events'),
				showRetry: true,
			};
		case 'refunded':
			return {
				eyebrow: __('Payment refunded', 'ctx-events'),
				title: __('Your payment was refunded', 'ctx-events'),
				message: __('If you still want to attend, please contact us before starting a new payment.', 'ctx-events'),
				showRetry: false,
			};
		default:
			return {
				eyebrow: __('Booking completed', 'ctx-events'),
				title: __('Booking confirmed!', 'ctx-events'),
				message: __('You will receive a confirmation email shortly.', 'ctx-events'),
				showRetry: false,
			};
	}
}

function formatIban(iban: string): string {
	return iban
		.replace(/\s/g, '')
		.replace(/(.{4})/g, '$1 ')
		.trim();
}

function renderPaymentDetails(payment: BookingPayment, reference: string) {
	if ('checkoutUrl' in payment) {
		return (
			<div className="booking-success__payment">
				<h3 className="booking-success__card-title">
					{__('Payment', 'ctx-events')}
				</h3>
				<p className="booking-success__note">
					{__('Complete your payment using the link below.', 'ctx-events')}
				</p>
				{payment.instructions && (
					<p className="booking-success__instructions">
						{payment.instructions}
					</p>
				)}
				<a className="ctx-button" href={payment.checkoutUrl}>
					{__('Continue to payment', 'ctx-events')}
				</a>
			</div>
		);
	}

	return (
		<div className="booking-success__payment booking-success__payment--bank">
			<h3 className="booking-success__card-title">
				{__('Bank transfer details', 'ctx-events')}
			</h3>
			<p className="booking-success__note">
				{__(
					'Please transfer the amount using the following bank details.',
					'ctx-events',
				)}
			</p>
			{payment.instructions && (
				<p className="booking-success__instructions">{payment.instructions}</p>
			)}
			<div className="booking-success__details">
				<div className="booking-success__detail">
					<span>{__('Amount', 'ctx-events')}</span>
					<strong>
						{formatPrice({
							amountCents: payment.amount.amountCents,
							currency: payment.amount.currency,
						})}
					</strong>
				</div>
				<div className="booking-success__detail">
					<span>{__('Account Holder', 'ctx-events')}</span>
					<strong>{payment.bankData.accountHolder}</strong>
				</div>
				<div className="booking-success__detail">
					<span>{__('Bank', 'ctx-events')}</span>
					<strong>{payment.bankData.bankName}</strong>
				</div>
				<div className="booking-success__detail">
					<span>{__('IBAN', 'ctx-events')}</span>
					<strong>{formatIban(payment.bankData.iban)}</strong>
				</div>
				<div className="booking-success__detail">
					<span>{__('BIC', 'ctx-events')}</span>
					<strong>{payment.bankData.bic}</strong>
				</div>
				<div className="booking-success__detail">
					<span>{__('Reference', 'ctx-events')}</span>
					<strong>{reference}</strong>
				</div>
			</div>
		</div>
	);
}

export function SuccessSection({
	reference,
	eventName,
	payment,
	customerEmailStatus,
	paymentStatus,
	onClose,
}: Props) {
	const customerEmailFailed =
		customerEmailStatus === 'failed' || customerEmailStatus === 'skipped';
	const showOfflineQr = payment !== null && !('checkoutUrl' in payment);
	const paymentQr = usePaymentQr(reference, showOfflineQr);
	const returnCopy = getReturnCopy(paymentStatus);
	const retryPaymentLink = buildPaymentLink(reference);

	return (
		<div
			className="booking-section booking-section--success"
			data-testid="booking-section-success"
		>
			<div className="booking-success">
				<BookingCard
					className="booking-success__hero booking-success__card"
					variant="surface"
				>
					<span className="booking-success__icon" aria-hidden="true">
						✓
					</span>
					<div className="booking-success__hero-copy">
						<p className="booking-success__eyebrow">
							{returnCopy.eyebrow}
						</p>
						<h2 className="booking-success__title">
							{returnCopy.title}
						</h2>
						<p className="booking-success__event">{eventName}</p>
						<div className="booking-success__reference-card">
							<span>{__('Your booking reference', 'ctx-events')}</span>
							<code>{reference}</code>
						</div>
					</div>
				</BookingCard>

				<div className="booking-success__layout">
					<div className="booking-success__main">
						<BookingCard className="booking-success__card" variant="surface">
							<h3 className="booking-success__card-title">
								{__('What happens next', 'ctx-events')}
							</h3>
							{payment ? (
								renderPaymentDetails(payment, reference)
							) : (
								<p className="booking-success__note">
									{paymentStatus !== null
										? returnCopy.message
										: customerEmailFailed
										? __(
												'There was a problem sending your confirmation email. Please contact us if you do not hear from us shortly.',
												'ctx-events',
											)
										: __(
												'You will receive a confirmation email shortly.',
												'ctx-events',
											)}
								</p>
							)}
							{paymentStatus !== null && returnCopy.showRetry && (
								<div className="booking-success__retry">
									<a className="ctx-button" href={retryPaymentLink}>
										{__('Continue to payment', 'ctx-events')}
									</a>
								</div>
							)}
							{payment && customerEmailFailed && (
								<p className="booking-success__note booking-error" role="alert">
									{__(
										'There was a problem sending your confirmation email. Please keep your booking reference and contact us if needed.',
										'ctx-events',
									)}
								</p>
							)}
						</BookingCard>

						<div className="booking-success__actions">
							<Button variant="secondary" onClick={onClose}>
								{__('Close', 'ctx-events')}
							</Button>
						</div>
					</div>

					{showOfflineQr && (
						<aside className="booking-success__aside">
							<BookingCard
								className="booking-success__qr booking-success__card"
								variant="surface"
							>
								<h3 className="booking-success__card-title">
									{__('Scan payment QR', 'ctx-events')}
								</h3>
								{paymentQr.status === 'loading' && (
									<p className="booking-success__note">
										{__('Loading payment QR…', 'ctx-events')}
									</p>
								)}
								{paymentQr.status === 'error' && (
									<p
										className="booking-success__note booking-error"
										role="alert"
									>
										{paymentQr.message}
									</p>
								)}
								{paymentQr.status === 'loaded' && paymentQr.qr && (
									<img
										className="booking-success__qr-image"
										src={paymentQr.qr.dataUri}
										alt={__('QR code for bank transfer payment', 'ctx-events')}
									/>
								)}
							</BookingCard>
						</aside>
					)}
				</div>
			</div>
		</div>
	);
}
