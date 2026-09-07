import type { DataTableAction } from '@events/datatable/types';
import { TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { Event } from '../../types/types';
import ActionModal from '../shared/ActionModal';

type Props = {
	action: DataTableAction;
	item: Event;
	onClose: () => void;
	onActionPerformed?: (items: Array<unknown>) => void;
};

const EventDuplicateModal = ({
	action,
	item,
	onClose,
	onActionPerformed,
}: Props) => {
	const [dates, setDates] = useState<Array<string>>(['']);
	const [isSubmitting, setIsSubmitting] = useState(false);
	const validDates = dates.filter((date) => date !== '');

	const updateDate = (index: number, value: string) => {
		setDates((currentDates) =>
			currentDates.map((date, dateIndex) =>
				dateIndex === index ? value : date,
			),
		);
	};

	const removeDate = (index: number) => {
		setDates((currentDates) => currentDates.filter((_, dateIndex) => dateIndex !== index));
	};

	const duplicateEvents = async () => {
		if (validDates.length === 0) {
			return;
		}

		setIsSubmitting(true);
		try {
			await action.callback([item], onActionPerformed, { dates: validDates });
			onClose();
		} finally {
			setIsSubmitting(false);
		}
	};

	return (
		<ActionModal
			title={
				typeof action.modalHeader === 'function'
					? action.modalHeader()
					: action.modalHeader
			}
			onClose={onClose}
			isBusy={isSubmitting}
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
						onClick={duplicateEvents}
						disabled={isSubmitting || validDates.length === 0}
					>
						{__('Duplicate', 'ctx-events')}
					</button>
				</>
			}
		>
			<p>{__('Create one draft copy for each selected date.', 'ctx-events')}</p>
			{dates.map((date, index) => (
				<div key={`date-${index}`} style={{ display: 'flex', gap: '8px' }}>
					<TextControl
						label={index === 0 ? __('Date', 'ctx-events') : undefined}
						type="date"
						value={date}
						onChange={(value) => updateDate(index, value)}
						disabled={isSubmitting}
					/>
					{dates.length > 1 && (
						<button
							type="button"
							className="button-link-delete"
							onClick={() => removeDate(index)}
							disabled={isSubmitting}
						>
							{__('Remove', 'ctx-events')}
						</button>
					)}
				</div>
			))}
			<button
				type="button"
				className="button-link"
				onClick={() => setDates((currentDates) => [...currentDates, ''])}
				disabled={isSubmitting}
			>
				{__('Add date', 'ctx-events')}
			</button>
		</ActionModal>
	);
};

export default EventDuplicateModal;
