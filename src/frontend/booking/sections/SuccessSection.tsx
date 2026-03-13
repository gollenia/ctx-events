import { formatPrice } from '@events/i18n';
import { __, sprintf } from '@wordpress/i18n';
import type { BookingPayment } from '../types';

type Props = {
	reference: string;
	eventName: string;
	payment: BookingPayment | null;
	onClose: () => void;
};

function formatIban(iban: string): string {
	return iban.replace(/\s/g, '').replace(/(.{4})/g, '$1 ').trim();
}

function renderPaymentDetails(payment: BookingPayment, reference: string) {
	if ('checkoutUrl' in payment) {
		return (
			<div className="booking-success__payment">
				<p className="booking-success__note">
					{__(
						'Complete your payment using the link below.',
						'ctx-events',
					)}
				</p>
				{payment.instructions && (
					<p className="booking-success__instructions">{payment.instructions}</p>
				)}
				<a
					className="booking-btn booking-btn--primary"
					href={payment.checkoutUrl}
				>
					{__('Continue to payment', 'ctx-events')}
				</a>
			</div>
		);
	}

	return (
		<div className="booking-success__payment booking-success__payment--bank">
			<p className="booking-success__note">
				{__('Please transfer the amount using the following bank details.', 'ctx-events')}
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
					<span>{__('Account holder', 'ctx-events')}</span>
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

export function SuccessSection({ reference, eventName, payment, onClose }: Props) {
	return (
		<div className="booking-section booking-section--success">
			<div className="booking-success">
				<span className="booking-success__icon" aria-hidden="true">
					✓
				</span>
				<h2 className="booking-success__title">{__('Booking confirmed!', 'ctx-events')}</h2>
				<p className="booking-success__event">{eventName}</p>
				<p className="booking-success__reference">
					{sprintf(
						// translators: %s is the booking reference number
						__('Your booking reference: %s', 'ctx-events'),
						reference,
					)}
				</p>
				{payment ? (
					renderPaymentDetails(payment, reference)
				) : (
					<p className="booking-success__note">
						{__('You will receive a confirmation email shortly.', 'ctx-events')}
					</p>
				)}
				<button type="button" className="booking-btn booking-btn--secondary" onClick={onClose}>
					{__('Close', 'ctx-events')}
				</button>
			</div>
		</div>
	);
}
