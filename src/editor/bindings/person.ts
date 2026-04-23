import { __ } from '@wordpress/i18n';
import { registerBlockBindingsSource } from '@wordpress/blocks';
import {
	type BindingConfig,
	type BindingField,
	type Context,
	getEventFromContext,
	getPersonRecordId,
	getRecordTitle,
	getRelatedRecord,
} from './shared';

registerBlockBindingsSource({
	name: 'ctx-events/person',
	usesContext: ['ctx-events/eventId', 'postId', 'postType'],
	getFieldsList(): BindingField[] {
		return [
			{ label: __('Name', 'ctx-events'), type: 'string', args: { field: 'name' } },
			{ label: __('Profile link', 'ctx-events'), type: 'string', args: { field: 'url' } },
			{ label: __('Image URL', 'ctx-events'), type: 'string', args: { field: 'imageUrl' } },
			{ label: __('Image alt text', 'ctx-events'), type: 'string', args: { field: 'imageAlt' } },
			{ label: __('Image ID', 'ctx-events'), type: 'number', args: { field: 'imageId' } },
		];
	},
	getValues({ bindings, context, select }) {
		const event = getEventFromContext(select, context as Context | undefined);
		if (!event) {
			return {};
		}

		const person = getRelatedRecord(
			select,
			'ctx-event-person',
			getPersonRecordId(event),
		);

		const values: Record<string, string | number> = {};

		Object.entries(bindings).forEach(([attributeName, binding]) => {
			const field =
				typeof binding === 'object' && binding !== null
					? (binding as BindingConfig).args?.field
					: undefined;

			if (field === 'name') {
				values[attributeName] = getRecordTitle(person);
			}

			if (field === 'url') {
				values[attributeName] = person?.link || '';
			}

			if (field === 'imageUrl') {
				values[attributeName] =
					person?._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
			}

			if (field === 'imageAlt') {
				values[attributeName] =
					person?._embedded?.['wp:featuredmedia']?.[0]?.alt_text ||
					getRecordTitle(person);
			}

			if (field === 'imageId') {
				values[attributeName] = Number(
					person?._embedded?.['wp:featuredmedia']?.[0]?.id || 0,
				);
			}
		});

		return values;
	},
});
