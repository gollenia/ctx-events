import type { DataTableAction } from '@events/datatable/types';
import { CheckboxControl, TextareaControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ActionModal from '../shared/ActionModal';

type BookingAction = DataTableAction & {
	confirmText: string;
	confirmLabel: string;
	supportsMail: boolean;
};

type Props = {
	action: DataTableAction;
	item: { reference: string };
	onClose: () => void;
	onActionPerformed?: (items: Array<any>) => void;
};

const BookingActionConfirmModal = ({
	action,
	item,
	onClose,
	onActionPerformed,
}: Props) => {
	const bookingAction = action as BookingAction;
	const [sendMail, setSendMail] = useState(true);
	const [cancellationReason, setCancellationReason] = useState('');
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleConfirm = async () => {
		setIsSubmitting(true);
		try {
			await bookingAction.callback([item], onActionPerformed, {
				sendMail,
				cancellationReason,
			});
			onClose();
		} finally {
			setIsSubmitting(false);
		}
	};

	return (
		<ActionModal
			title={
				typeof bookingAction.modalHeader === 'function'
					? bookingAction.modalHeader()
					: bookingAction.modalHeader
			}
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
						{__('Cancel', 'ctx-events')}
					</button>
					<button
						type="button"
						className="components-button is-primary"
						onClick={handleConfirm}
						disabled={isSubmitting}
					>
						{bookingAction.confirmLabel}
					</button>
				</>
			}
		>
			<p>{bookingAction.confirmText}</p>

			{bookingAction.supportsMail ? (
				<>
					<CheckboxControl
						label={__('Send email notification', 'ctx-events')}
						checked={sendMail}
						onChange={setSendMail}
						disabled={isSubmitting}
					/>
					{bookingAction.id === 'cancel' ? (
						<TextareaControl
							label={__('Cancellation reason', 'ctx-events')}
							help={__(
								'Optional. Available in email templates via {{booking.cancellation_reason}}.',
								'ctx-events',
							)}
							value={cancellationReason}
							onChange={setCancellationReason}
							disabled={isSubmitting}
						/>
					) : null}
				</>
			) : null}
		</ActionModal>
	);
};

export default BookingActionConfirmModal;
