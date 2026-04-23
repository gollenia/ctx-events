import { __ } from '@wordpress/i18n';
import { registerBlockBindingsSource } from '@wordpress/blocks';
import { formatDateRange, formatTimeRange } from '@events/i18n';
import {
	type BindingConfig,
	type BindingField,
	type Context,
	getEventFromContext,
	stripHtml,
} from './shared';

registerBlockBindingsSource({
	name: 'ctx-events/event',
	usesContext: ['ctx-events/eventId', 'postId', 'postType'],
	getFieldsList(): BindingField[] {
		return [
			{ label: __('Title', 'ctx-events'), type: 'string', args: { field: 'title' } },
			{ label: __('Excerpt', 'ctx-events'), type: 'string', args: { field: 'excerpt' } },
			{ label: __('Schedule', 'ctx-events'), type: 'string', args: { field: 'schedule' } },
			{ label: __('Date', 'ctx-events'), type: 'string', args: { field: 'dateLabel' } },
			{ label: __('Time', 'ctx-events'), type: 'string', args: { field: 'timeLabel' } },
			{ label: __('Link', 'ctx-events'), type: 'string', args: { field: 'link' } },
			{
				label: __('Featured image URL', 'ctx-events'),
				type: 'string',
				args: { field: 'imageUrl' },
			},
			{
				label: __('Featured image alt text', 'ctx-events'),
				type: 'string',
				args: { field: 'imageAlt' },
			},
			{
				label: __('Featured image ID', 'ctx-events'),
				type: 'number',
				args: { field: 'imageId' },
			},
		];
	},
	getValues({ bindings, context, select }) {
		const event = getEventFromContext(select, context as Context | undefined);
		if (!event) {
			return {};
		}

		const values: Record<string, string | number> = {};

		Object.entries(bindings).forEach(([attributeName, binding]) => {
			const field =
				typeof binding === 'object' && binding !== null
					? (binding as BindingConfig).args?.field
					: undefined;

			if (field === 'title') {
				values[attributeName] = event.title?.raw || event.title?.rendered || '';
			}

			if (field === 'dateLabel') {
				const start = event.meta?._event_start || '';
				const end = event.meta?._event_end || '';
				values[attributeName] = start ? formatDateRange(start, end || start) : '';
			}

			if (field === 'timeLabel') {
				const start = event.meta?._event_start || '';
				const end = event.meta?._event_end || '';
				values[attributeName] = start ? formatTimeRange(start, end || start) : '';
			}

			if (field === 'imageAlt') {
				values[attributeName] =
					event._embedded?.['wp:featuredmedia']?.[0]?.alt_text || '';
			}

			if (field === 'imageId') {
				values[attributeName] = Number(event.featured_media || 0);
			}

			if (field === 'imageUrl') {
				values[attributeName] =
					event._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
			}

			if (field === 'schedule') {
				const start = event.meta?._event_start || '';
				const end = event.meta?._event_end || '';
				const date = start ? formatDateRange(start, end || start) : '';
				const time = start ? formatTimeRange(start, end || start) : '';
				values[attributeName] = time ? `${date}, ${time}` : date;
			}

			if (field === 'excerpt') {
				values[attributeName] = stripHtml(event.excerpt?.rendered);
			}

			if (field === 'link') {
				values[attributeName] = event.link || '';
			}
		});

		return values;
	},
});
