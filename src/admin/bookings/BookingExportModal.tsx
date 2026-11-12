import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ActionModal from '../shared/ActionModal';

type Props = {
	onClose: () => void;
	onExport: (includeAttendees: boolean, statuses: number[]) => void;
};

const BookingExportModal = ({ onClose, onExport }: Props) => {
	const [includeAttendees, setIncludeAttendees] = useState(false);
	const [statuses, setStatuses] = useState<number[]>([2]);

	const handleExport = () => {
		onExport(includeAttendees, statuses);
		onClose();
	};

	const toggleStatus = (status: number, checked: boolean) => {
		setStatuses((current) =>
			checked
				? [...current, status]
				: current.length === 1
					? current
					: current.filter((item) => item !== status),
		);
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

			<p>{__('Include booking statuses', 'ctx-events')}</p>
			{[
				{ value: 2, label: __('Approved', 'ctx-events') },
				{ value: 1, label: __('Pending', 'ctx-events') },
				{ value: 3, label: __('Canceled', 'ctx-events') },
				{ value: 4, label: __('Expired', 'ctx-events') },
			].map((status) => (
				<CheckboxControl
					key={status.value}
					label={status.label}
					checked={statuses.includes(status.value)}
					onChange={(checked) => toggleStatus(status.value, checked)}
				/>
			))}

		</ActionModal>
	);
};

export default BookingExportModal;
