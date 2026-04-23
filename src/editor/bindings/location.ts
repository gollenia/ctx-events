import { __ } from '@wordpress/i18n';
import { registerBlockBindingsSource } from '@wordpress/blocks';
import {
	type BindingConfig,
	type BindingField,
	type Context,
	getEventFromContext,
	getRelatedRecord,
} from './shared';

registerBlockBindingsSource({
	name: 'ctx-events/location',
	usesContext: ['ctx-events/eventId', 'postId', 'postType'],
	getFieldsList(): BindingField[] {
		return [
			{ label: __('Name', 'ctx-events'), type: 'string', args: { field: 'name' } },
			{ label: __('Link', 'ctx-events'), type: 'string', args: { field: 'url' } },
		];
	},
	getValues({ bindings, context, select }) {
		const event = getEventFromContext(select, context as Context | undefined);
		if (!event) {
			return {};
		}

		const location = getRelatedRecord(
			select,
			'ctx-event-location',
			Number(event.meta?._location_id ?? 0),
		);

		const values: Record<string, string> = {};

		Object.entries(bindings).forEach(([attributeName, binding]) => {
			const field =
				typeof binding === 'object' && binding !== null
					? (binding as BindingConfig).args?.field
					: undefined;

			if (field === 'name') {
				values[attributeName] =
					location?.title?.raw || location?.title?.rendered || '';
			}

			if (field === 'url') {
				values[attributeName] = location?.link || '';
			}
		});

		return values;
	},
});
