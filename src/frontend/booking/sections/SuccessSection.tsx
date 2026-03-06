import { __, sprintf } from '@wordpress/i18n';

type Props = {
	reference: string;
	eventName: string;
	onClose: () => void;
};

export function SuccessSection({ reference, eventName, onClose }: Props) {
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
				<p className="booking-success__note">
					{__('You will receive a confirmation email shortly.', 'ctx-events')}
				</p>
				<button type="button" className="booking-btn booking-btn--secondary" onClick={onClose}>
					{__('Close', 'ctx-events')}
				</button>
			</div>
		</div>
	);
}
