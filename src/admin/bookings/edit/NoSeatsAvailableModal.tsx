import { Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type Props = {
	onClose: () => void;
};

const NoSeatsAvailableModal = ({ onClose }: Props) => (
	<Modal
		title={__('No seats available', 'ctx-events')}
		onRequestClose={onClose}
		className="booking-edit__attendee-modal"
		shouldCloseOnClickOutside={false}
	>
		<div className="booking-edit__attendee-modal-body">
			<p>
				{__(
					'This event currently has no free seats for additional attendees.',
					'ctx-events',
				)}
			</p>
			<p>
				{__(
					'Cancel an attendee or increase capacity before adding another participant.',
					'ctx-events',
				)}
			</p>

			<div className="booking-edit__footer booking-edit__attendee-modal-actions">
				<button
					type="button"
					className="components-button is-primary"
					onClick={onClose}
				>
					{__('Close', 'ctx-events')}
				</button>
			</div>
		</div>
	</Modal>
);

export default NoSeatsAvailableModal;
