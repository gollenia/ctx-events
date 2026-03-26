import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ActionModal from '../shared/ActionModal';

type Props = {
	onClose: () => void;
	onExport: (includeAttendees: boolean) => void;
};

const BookingExportModal = ({ onClose, onExport }: Props) => {
	const [includeAttendees, setIncludeAttendees] = useState(false);

	const handleExport = () => {
		onExport(includeAttendees);
		onClose();
	};

	return (
		<ActionModal
			title={__('Export bookings', 'ctx-events')}
			onClose={onClose}
			onConfirm={handleExport}
			className="booking-export-modal"
			footer={
				<>
					<button
						type="button"
						className="components-button is-secondary"
						onClick={onClose}
					>
						{__('Cancel', 'ctx-events')}
					</button>
					<button
						type="button"
						className="components-button is-primary"
						onClick={handleExport}
					>
						{__('Export Excel', 'ctx-events')}
					</button>
				</>
			}
		>
			<p>
				{__(
					'Choose which booking data should be included in the export.',
					'ctx-events',
				)}
			</p>

			<CheckboxControl
				label={__('Include attendees', 'ctx-events')}
				checked={includeAttendees}
				onChange={setIncludeAttendees}
			/>

		</ActionModal>
	);
};

export default BookingExportModal;
