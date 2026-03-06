import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useDataTable } from './DataTableContext';
import Search from './Search';
import type {
	DataFieldConfig,
	DataFilterConfig,
	DataFilterField,
	DataViewConfig,
} from './types';

interface DataFilterProps {
	fields: Array<DataFieldConfig>;
	view: DataViewConfig;
	onChangeView: (updates: Partial<DataViewConfig>) => void;
}

type FilterableField = DataFieldConfig & { filterBy: DataFilterConfig };

const Filter = ({ fields, view, onChangeView }: DataFilterProps) => {
	const filterableFields: Array<FilterableField> = useMemo(
		() =>
			fields?.filter((field): field is FilterableField =>
				Boolean(field.filterBy),
			) || [],
		[fields],
	);

	const onChangeFilter = (id: string, value: string | number | boolean) => {
		const otherFilters: Array<DataFilterField> =
			view.filters?.filter((f: DataFilterField) => f.field !== id) || [];
		const nextFilters = value
			? [...otherFilters, { field: id, operator: 'is', value }]
			: otherFilters;
		onChangeView({ filters: nextFilters });
	};

	const renderInput = (fieldId: string, filterConfig: DataFilterConfig) => {
		const currentFilterValue =
			view.filters?.find((filters) => filters.field === fieldId)?.value || '';

		switch (filterConfig.type) {
			case 'integer':
			case 'number':
			case 'array':
			case 'date':
			case 'text':
				return (
					<select
						key={fieldId}
						name={fieldId}
						value={currentFilterValue.toString()}
						onChange={(e) => onChangeFilter(fieldId, e.target.value)}
						className="postform"
						multiple={filterConfig.type === 'array'}
						style={{ marginRight: '5px' }}
					>
						{filterConfig.label && (
							<option value="">{filterConfig.label}</option>
						)}

						{filterConfig.elements?.map((element) => (
							<option key={element.value} value={element.value}>
								{element.label}
							</option>
						))}
					</select>
				);

			case 'boolean':
				return (
					<label key={fieldId} style={{ marginRight: '10px' }}>
						<input
							type="checkbox"
							checked={!!view.filters?.find((f) => f.field === fieldId)?.value}
							onChange={(e) => onChangeFilter(fieldId, e.target.checked)}
							style={{ marginRight: '5px' }}
						/>
						{filterConfig.label}
					</label>
				);

			default:
				return null;
		}
	};

	return (
		<div className="tablenav top">
			<div className="alignleft actions bulkactions">
				{filterableFields.map((field: FilterableField) =>
					renderInput(field.id, field.filterBy),
				)}
			</div>
			<Search view={view} onChangeView={onChangeView} />
		</div>
	);
};

const DataTableFilter = () => {
	const { fields, view, onChangeView } = useDataTable();
	if (!view || !onChangeView) return null;

	return <Filter fields={fields} view={view} onChangeView={onChangeView} />;
};

export default Filter;
export { DataTableFilter };
export type { DataFilterField, DataFilterProps };
