import { __ } from '@wordpress/i18n';
import { useDataTable } from './DataTableContext';
import type {
	DataFieldConfig,
	DataViewConfig,
	DataViewOption,
} from './types';

interface ScreenMetaProps {
	context: string;
	fields: Array<DataFieldConfig>;
	view: DataViewConfig;
	onChangeView?: (updates: Partial<DataViewConfig>) => void;
	views?: Array<DataViewOption>;
}

const ScreenMeta = ({
	context,
	fields,
	view,
	onChangeView,
	views = [],
}: ScreenMetaProps) => {
	const activeView = view.type ?? 'table';
	const availableViews = views.filter((option) => option.id !== 'table');
	const showViews = availableViews.length > 0;

	return (
		<div
			id="screen-meta"
			className="metabox-prefs"
			style={{ display: context === '' ? 'none' : 'block' }}
		>
			<div
				id="screen-options-wrap"
				className="hidden"
				tabIndex={-1}
				style={{ display: context === 'options' ? 'block' : 'none' }}
			>
				<form id="adv-settings" method="post">
					<fieldset className="metabox-prefs">
						<legend>Spalten</legend>
						{fields.map((field) => (
							<label key={field.id}>
								<input
									disabled={field.enableHiding === false}
									className="hide-column-tog"
									type="checkbox"
									id={`${field.id}-hide`}
									name={`${field.id}-hide`}
									checked={view.fields?.includes(field.id) ?? true}
									onChange={() => {
										if (onChangeView) {
											const newFields = view.fields?.includes(field.id)
												? view.fields.filter((f) => f !== field.id)
												: [...(view.fields || []), field.id];
											onChangeView({ fields: newFields });
										}
									}}
								/>
								{field.label}
							</label>
						))}
					</fieldset>
					<fieldset className="screen-options">
						<legend>Seitennummerierung</legend>
						<label htmlFor="edit_post_per_page">
							{__('Entries per page', 'ctx-events')}
						</label>
						<input
							type="number"
							step="1"
							min="1"
							max="999"
							className="screen-per-page"
							name="wp_screen_options[value]"
							id="edit_post_per_page"
							value={view.perPage}
							onChange={(e) =>
								onChangeView?.({ perPage: parseInt(e.target.value, 10) })
							}
						/>
					</fieldset>
					{showViews && (
						<fieldset className="views">
							<legend>{__('Views', 'ctx-events')}</legend>
							<label>
								<input
									type="radio"
									name="ctx-datatable-view"
									value="table"
									checked={activeView === 'table'}
									onChange={() => onChangeView?.({ type: 'table' })}
								/>
								{__('Table', 'ctx-events')}
							</label>
							{availableViews.map((option) => (
								<label key={option.id}>
									<input
										type="radio"
										name="ctx-datatable-view"
										value={option.id}
										checked={activeView === option.id}
										onChange={() => onChangeView?.({ type: option.id })}
									/>
									{option.label}
								</label>
							))}
						</fieldset>
					)}
				</form>
			</div>{' '}
		</div>
	);
};

const DataTableScreenMeta = () => {
	const { screenMetaContext, view, onChangeView, fields, views } =
		useDataTable();
	return (
		<ScreenMeta
			context={screenMetaContext}
			fields={fields}
			view={view}
			onChangeView={onChangeView}
			views={views}
		/>
	);
};

export default ScreenMeta;
export { DataTableScreenMeta };
