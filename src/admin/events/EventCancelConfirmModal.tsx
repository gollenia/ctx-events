import type { DataTableAction } from '@events/datatable/types';
import type { Event } from '../../types/types';
import { CheckboxControl, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

type EventCancelAction = DataTableAction & {
	confirmText: (item: Event) => string;
	confirmLabel: string;
};

type Props = {
	action: DataTableAction;
	item: Event;
	onClose: () => void;
	onActionPerformed?: (items: Array<any>) => void;
};

const EventCancelConfirmModal = ({
	action,
	item,
	onClose,
	onActionPerformed,
}: Props) => {
	const cancelAction = action as EventCancelAction;
	const [notifyAttendees, setNotifyAttendees] = useState(true);
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleConfirm = async () => {
		setIsSubmitting(true);
		try {
			await cancelAction.callback([item], onActionPerformed, {
				notifyAttendees,
			});
			onClose();
		} finally {
			setIsSubmitting(false);
		}
	};

	return (
		<Modal
			title={
				typeof cancelAction.modalHeader === 'function'
					? cancelAction.modalHeader()
					: cancelAction.modalHeader
			}
			onRequestClose={isSubmitting ? undefined : onClose}
			shouldCloseOnClickOutside={!isSubmitting}
			className="booking-action-confirm-modal"
		>
			<div className="booking-action-confirm-modal__body">
				<p>{cancelAction.confirmText(item)}</p>

				<CheckboxControl
					label={__('Notify attendees by email', 'ctx-events')}
					checked={notifyAttendees}
					onChange={setNotifyAttendees}
					disabled={isSubmitting}
				/>

				<div className="booking-edit__footer booking-action-confirm-modal__actions">
					<button
						type="button"
						className="components-button is-secondary"
						onClick={onClose}
						disabled={isSubmitting}
					>
						{__('Keep event', 'ctx-events')}
					</button>
					<button
						type="button"
						className="components-button is-primary"
						onClick={handleConfirm}
						disabled={isSubmitting}
					>
						{cancelAction.confirmLabel}
					</button>
				</div>
			</div>
		</Modal>
	);
};

export default EventCancelConfirmModal;
