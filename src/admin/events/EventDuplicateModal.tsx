import type { DataTableAction } from '@events/datatable/types';
import { Button, TextControl } from '@wordpress/components';
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { trash } from '@wordpress/icons';
import type { Event } from '../../types/types';
import ActionModal from '../shared/ActionModal';
import SmallDataTable, {
	type SmallDataTableField,
} from '../../shared/small-data-table';

type Props = {
	action: DataTableAction;
	item: Event;
	onClose: () => void;
	onActionPerformed?: (items: Array<unknown>) => void;
};

type DuplicateDateRow = {
	id: number;
	date: string;
};

const EventDuplicateModal = ({
	action,
	item,
	onClose,
	onActionPerformed,
}: Props) => {
	const nextDateId = useRef(2);
	const [dateRows, setDateRows] = useState<Array<DuplicateDateRow>>([
		{ id: 1, date: '' },
	]);
	const [isSubmitting, setIsSubmitting] = useState(false);
	const validDates = dateRows
		.map((row) => row.date)
		.filter((date) => date !== '');

	const updateDate = (id: number, value: string) => {
		setDateRows((currentRows) =>
			currentRows.map((row) => (row.id === id ? { ...row, date: value } : row)),
		);
	};

	const removeDate = (id: number) => {
		setDateRows((currentRows) => currentRows.filter((row) => row.id !== id));
	};

	const addDate = () => {
		setDateRows((currentRows) => [
			...currentRows,
			{ id: nextDateId.current++, date: '' },
		]);
	};

	const fields: Array<SmallDataTableField<DuplicateDateRow>> = [
		{
			id: 'date',
			label: __('Date', 'ctx-events'),
			render: (row, index) => (
				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					hideLabelFromVision
					label={`${__('Date', 'ctx-events')} ${index + 1}`}
					type="date"
					value={row.date}
					disabled={isSubmitting}
					onChange={(value) => updateDate(row.id, value)}
				/>
			),
		},
		{
			id: 'actions',
			ariaLabel: __('Actions', 'ctx-events'),
			headerStyle: { width: '1%' },
			render: (row) =>
				dateRows.length > 1 ? (
					<Button
						isDestructive
						label={__('Remove', 'ctx-events')}
						icon={trash}
						size="small"
						disabled={isSubmitting}
						onClick={() => removeDate(row.id)}
					/>
				) : null,
		},
	];

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
			onConfirm={duplicateEvents}
			footer={
				<>
					<Button variant="secondary" onClick={onClose} disabled={isSubmitting}>
						{__('Cancel', 'ctx-events')}
					</Button>
					<Button
						variant="primary"
						onClick={duplicateEvents}
						disabled={isSubmitting || validDates.length === 0}
					>
						{__('Duplicate', 'ctx-events')}
					</Button>
				</>
			}
		>
			<p>
				{__('Create one published event for each selected date.', 'ctx-events')}
			</p>
			<SmallDataTable
				className="ctx-small-data-table widefat striped"
				fields={fields}
				getRowKey={(row) => row.id}
				items={dateRows}
			/>
			<Button variant="secondary" onClick={addDate} disabled={isSubmitting}>
				{__('Add date', 'ctx-events')}
			</Button>
		</ActionModal>
	);
};

export default EventDuplicateModal;
