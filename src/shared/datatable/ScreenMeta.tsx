import { fields } from 'src/admin/events/fields';
import type { DataFieldConfig, DataViewConfig } from './types';

interface ScreenMetaProps {
	context: string;
	fields: Array<DataFieldConfig>;
	view: DataViewConfig;
	onChangeView?: (updates: Partial<DataViewConfig>) => void;
}

const ScreenMeta = ({
	context,
	fields,
	view,
	onChangeView,
}: ScreenMetaProps) => {
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
						<label htmlFor="edit_post_per_page">Einträge pro Seite:</label>
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
				</form>
			</div>{' '}
		</div>
	);
};

export default ScreenMeta;
