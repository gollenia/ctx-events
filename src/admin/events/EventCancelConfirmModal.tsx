import type { DataTableAction } from '@events/datatable/types';
import { CheckboxControl, TextareaControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { Event } from '../../types/types';
import ActionModal from '../shared/ActionModal';

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
	const [cancellationReason, setCancellationReason] = useState('');
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleConfirm = async () => {
		setIsSubmitting(true);
		try {
			await cancelAction.callback([item], onActionPerformed, {
				notifyAttendees,
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
				typeof cancelAction.modalHeader === 'function'
					? cancelAction.modalHeader()
					: cancelAction.modalHeader
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
				</>
			}
		>
			<p>{cancelAction.confirmText(item)}</p>

			<CheckboxControl
				label={__('Notify attendees by email', 'ctx-events')}
				checked={notifyAttendees}
				onChange={setNotifyAttendees}
				disabled={isSubmitting}
			/>

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
		</ActionModal>
	);
};

export default EventCancelConfirmModal;
