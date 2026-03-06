import { __ } from '@wordpress/i18n';
import { useDataTable } from './DataTableContext';
import type { DataStatusItem, DataViewConfig } from './types';

type StatusSelectProps = {
	statusItems?: Array<DataStatusItem>;
	view: DataViewConfig;
	onViewChange: (updates: Partial<DataViewConfig>) => void;
};

const StatusSelect = ({
	statusItems = [],
	view,
	onViewChange,
}: StatusSelectProps) => {
	if (statusItems.length === 0) return null;
	const currentStatus =
		view.filters?.find((f) => f.field === 'status')?.value || 'publish';

	const handleStatusChange = (newStatus: string) => {
		const otherFilters =
			view.filters?.filter((f) => f.field !== 'status') || [];

		const nextFilters =
			newStatus === 'publish'
				? otherFilters
				: [
						...otherFilters,
						{ field: 'status', operator: 'is', value: newStatus },
					];

		onViewChange({
			...view,
			filters: nextFilters,
			page: 1,
		});
	};

	return (
		<ul className="subsubsub">
			{statusItems
				.filter(
					(item: DataStatusItem) =>
						typeof item.count === 'number' && item.count > 0,
				)
				.map((item, index, visibleItems) => {
					return (
						<li key={item.value}>
							<a
								href={`#${item.value}`}
								className={currentStatus === item.value ? 'current' : ''}
								onClick={(e) => {
									e.preventDefault();
									handleStatusChange(item.value);
								}}
							>
								{item.label}
							</a>{' '}
							({item.count}){index < visibleItems.length - 1 && ' | '}
						</li>
					);
				})}
		</ul>
	);
};

const DataTableStatusSelect = () => {
	const { view, onChangeView, availableStatusItems, isLoading } =
		useDataTable();

	if (!view || !onChangeView) return null;

	if (isLoading) {
		return (
			<ul className="subsubsub">
				<li>
					<span className="current">{__('Loading...', 'events')}</span>
				</li>
			</ul>
		);
	}

	return (
		<StatusSelect
			statusItems={availableStatusItems}
			view={view}
			onViewChange={onChangeView}
		/>
	);
};

export default StatusSelect;
export { DataTableStatusSelect };
export type { StatusSelectProps };
