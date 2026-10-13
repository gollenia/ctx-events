import { Button, CheckboxControl, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import SmallDataTable, {
	type SmallDataTableField,
} from '../../../shared/small-data-table';

export type UpcomingTableColumnOption = {
	value: string;
	label: string;
};

type Props = {
	columns: UpcomingTableColumnOption[];
	selectedColumns: string[];
	onClose: () => void;
	onApply: (columns: string[]) => void;
};

const ColumnSettingsModal = ({
	columns,
	selectedColumns,
	onClose,
	onApply,
}: Props) => {
	const [draftColumns, setDraftColumns] = useState(selectedColumns);
	const [orderedColumns, setOrderedColumns] = useState(() => [
		...selectedColumns
			.map((column) => columns.find((option) => option.value === column))
			.filter((option): option is UpcomingTableColumnOption => Boolean(option)),
		...columns.filter((option) => !selectedColumns.includes(option.value)),
	]);
	const isEnabled = (option: UpcomingTableColumnOption) =>
		draftColumns.includes(option.value);
	const allEnabled =
		orderedColumns.length > 0 && draftColumns.length === orderedColumns.length;
	const someEnabled = draftColumns.length > 0 && !allEnabled;
	const toggleColumn = (column: string, checked: boolean) => {
		setDraftColumns((current) =>
			checked
				? [...current, column]
				: current.filter((value) => value !== column),
		);
	};
	const toggleAllColumns = (checked: boolean) => {
		setDraftColumns(
			checked ? orderedColumns.map((option) => option.value) : [],
		);
	};
	const fields: SmallDataTableField<UpcomingTableColumnOption>[] = [
		{
			id: 'drag',
			ariaLabel: __('Drag to reorder', 'ctx-events'),
			headerStyle: { width: '2rem' },
			cellClassName: 'ctx-small-data-table__drag-handle',
			render: () => <span aria-hidden="true">⠿</span>,
		},
		{
			id: 'enabled',
			ariaLabel: __('Enabled', 'ctx-events'),
			headerStyle: { width: '2rem' },
			label: (
				<CheckboxControl
					label=""
					checked={allEnabled}
					indeterminate={someEnabled}
					onChange={toggleAllColumns}
				/>
			),
			render: (option) => (
				<CheckboxControl
					label=""
					checked={isEnabled(option)}
					onChange={(checked) => toggleColumn(option.value, checked)}
				/>
			),
		},
		{
			id: 'column',
			label: __('Column', 'ctx-events'),
			render: (option) => option.label,
		},
	];

	return (
		<Modal
			title={__('Configure columns', 'ctx-events')}
			onRequestClose={onClose}
		>
			<p>
				{__(
					'Select visible columns and arrange them by drag and drop.',
					'ctx-events',
				)}
			</p>
			<SmallDataTable
				items={orderedColumns}
				fields={fields}
				getRowKey={(option) => option.value}
				draggable
				onReorder={setOrderedColumns}
				className="widefat striped"
			/>
			<div className="ctx-events-table-column-modal__footer">
				<Button variant="tertiary" onClick={onClose}>
					{__('Cancel', 'ctx-events')}
				</Button>
				<Button
					variant="primary"
					onClick={() =>
						onApply(
							orderedColumns
								.filter((option) => draftColumns.includes(option.value))
								.map((option) => option.value),
						)
					}
				>
					{__('Apply', 'ctx-events')}
				</Button>
			</div>
		</Modal>
	);
};

export default ColumnSettingsModal;
