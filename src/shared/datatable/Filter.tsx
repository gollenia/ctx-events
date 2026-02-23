import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
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
				return (
					<select
						key={fieldId}
						name={fieldId}
						value={currentFilterValue.toString()}
						onChange={(e) => onChangeFilter(fieldId, e.target.value)}
						className="postform"
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

			case 'text':
				return (
					<input
						key={fieldId}
						type="search"
						placeholder={filterConfig.label}
						value={currentFilterValue.toString()}
						onChange={(e) => onChangeFilter(fieldId, e.target.value)}
						className="form-control"
						style={{ marginRight: '5px', height: '30px' }}
					/>
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
			<p className="search-box">
				<label className="screen-reader-text" htmlFor="post-search-input">
					{__('Search Items', 'ctx-events')}
				</label>
				<input
					type="search"
					id="post-search-input"
					name="s"
					value={view.search}
					onChange={(e) => onChangeView({ search: e.target.value })}
				/>
				<input
					type="submit"
					id="search-submit"
					className="button"
					value={__('Search Items', 'ctx-events')}
				/>
			</p>
		</div>
	);
};

export default Filter;
export type { DataFilterField, DataFilterProps };
