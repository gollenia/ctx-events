import type { DataTableAction } from '@events/datatable/types';
import { CheckboxControl, Flex, Modal } from '@wordpress/components';
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
			<Flex direction="column" gap="1rem">
				<Flex
					direction="column"
					justify="center"
					className="booking-action-confirm-modal__icon"
				>
					<p>{bookingAction.confirmText}</p>

					{bookingAction.supportsMail ? (
						<CheckboxControl
							label={__('Send email notification', 'ctx-events')}
							checked={sendMail}
							onChange={setSendMail}
							disabled={isSubmitting}
						/>
					) : null}
				</Flex>

				<Flex
					justify="flex-end"
					gap="1rem"
					className="booking-action-confirm-modal__footer"
				>
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
				</Flex>
			</Flex>
		</Modal>
	);
};

export default BookingActionConfirmModal;
