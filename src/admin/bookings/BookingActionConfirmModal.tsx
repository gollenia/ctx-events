import type { DataTableAction } from '@events/datatable/types';
import { CheckboxControl, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

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
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleConfirm = async () => {
		setIsSubmitting(true);
		try {
			await bookingAction.callback([item], onActionPerformed, { sendMail });
			onClose();
		} finally {
			setIsSubmitting(false);
		}
	};

	return (
		<Modal
			title={
				typeof bookingAction.modalHeader === 'function'
					? bookingAction.modalHeader()
					: bookingAction.modalHeader
			}
			onRequestClose={isSubmitting ? undefined : onClose}
			shouldCloseOnClickOutside={!isSubmitting}
			className="booking-action-confirm-modal"
		>
			<div className="booking-action-confirm-modal__body">
				<p>{bookingAction.confirmText}</p>

				{bookingAction.supportsMail ? (
					<CheckboxControl
						label={__('Send email notification', 'ctx-events')}
						checked={sendMail}
						onChange={setSendMail}
						disabled={isSubmitting}
					/>
				) : null}

				<div className="booking-edit__footer booking-action-confirm-modal__actions">
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
				</div>
			</div>
		</Modal>
	);
};

export default BookingActionConfirmModal;
