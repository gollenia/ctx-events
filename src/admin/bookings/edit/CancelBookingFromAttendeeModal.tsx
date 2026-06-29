import { CheckboxControl } from '@wordpress/components';
import { __, _x } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import ActionModal from '../../shared/ActionModal';

type Props = {
	onClose: () => void;
	onConfirm: (options: { sendMail: boolean }) => Promise<void>;
};

const CancelBookingFromAttendeeModal = ({ onClose, onConfirm }: Props) => {
	const [sendMail, setSendMail] = useState(true);
	const [isSubmitting, setIsSubmitting] = useState(false);

	const handleConfirm = async () => {
		setIsSubmitting(true);
		try {
			await onConfirm({ sendMail });
			onClose();
		} finally {
			setIsSubmitting(false);
		}
	};

	return (
		<ActionModal
			title={_x('Cancel booking', 'booking action title', 'ctx-events')}
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
						{_x('Cancel booking', 'booking action button', 'ctx-events')}
					</button>
				</>
			}
		>
			<p>
				{__(
					'This is the last active attendee. Continuing will cancel the entire booking.',
					'ctx-events',
				)}
			</p>

			<CheckboxControl
				label={__('Notify user', 'ctx-events')}
				checked={sendMail}
				onChange={setSendMail}
				disabled={isSubmitting}
			/>
		</ActionModal>
	);
};

export default CancelBookingFromAttendeeModal;
