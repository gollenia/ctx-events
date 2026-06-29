import { formatPrice } from '@events/i18n';
import { CheckboxControl, TextControl } from '@wordpress/components';
import { sprintf, __, _x } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import type { BookingAttendeeResource } from 'src/types/types';
import ActionModal from '../../shared/ActionModal';

type Props = {
	attendee: BookingAttendeeResource;
	onClose: () => void;
	onConfirm: (options: {
		sendMail: boolean;
		cancellationAmountCents: number;
	}) => Promise<void>;
};

const CancelAttendeeModal = ({ attendee, onClose, onConfirm }: Props) => {
	const [sendMail, setSendMail] = useState(true);
	const [cancellationAmount, setCancellationAmount] = useState('0');
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleConfirm = async () => {
		const parsedAmount = Number.parseFloat(cancellationAmount);
		const cancellationAmountCents = Number.isFinite(parsedAmount)
			? Math.max(0, Math.round(parsedAmount * 100))
			: 0;

		setIsSubmitting(true);
		try {
			await onConfirm({ sendMail, cancellationAmountCents });
			onClose();
		} finally {
			setIsSubmitting(false);
		}
	};

	return (
		<ActionModal
			title={_x('Cancel attendee', 'booking action title', 'ctx-events')}
			onClose={onClose}
			isBusy={isSubmitting}
			onConfirm={handleConfirm}
			footer={
				<>
					<button
						type="button"
						className="components-button is-secondary"
						onClick={onClose}
						disabled={isSubmitting}
					>
						{_x('Cancel', 'dialog action: abort and close modal', 'ctx-events')}
					</button>
					<button
						type="button"
						className="components-button is-primary"
						onClick={handleConfirm}
						disabled={isSubmitting}
					>
						{_x('Cancel attendee', 'booking action button', 'ctx-events')}
					</button>
				</>
			}
		>
			<p>
				{__(
					'This attendee will be cancelled immediately and cannot be reactivated later.',
					'ctx-events',
				)}
			</p>

			<CheckboxControl
				label={__('Notify user', 'ctx-events')}
				checked={sendMail}
				onChange={setSendMail}
				disabled={isSubmitting}
			/>

			<TextControl
				label={__('Cancellation fee', 'ctx-events')}
				type="number"
				min="0"
				step="0.01"
				value={cancellationAmount}
				onChange={setCancellationAmount}
				help={sprintf(
					__('Current attendee price: %s', 'ctx-events'),
					formatPrice(attendee.ticketPrice),
				)}
				disabled={isSubmitting}
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>
		</ActionModal>
	);
};

export default CancelAttendeeModal;
