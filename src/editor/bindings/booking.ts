import { __ } from '@wordpress/i18n';
import { registerBlockBindingsSource } from '@wordpress/blocks';
import {
	type BindingConfig,
	type BindingField,
	type Context,
	getEventFromContext,
	getPriceLabel,
} from './shared';

registerBlockBindingsSource({
	name: 'ctx-events/booking',
	usesContext: ['ctx-events/eventId', 'postId', 'postType'],
	getFieldsList(): BindingField[] {
		return [
			{ label: __('Status', 'ctx-events'), type: 'string', args: { field: 'statusLabel' } },
			{ label: __('Price', 'ctx-events'), type: 'string', args: { field: 'priceLabel' } },
			{
				label: __('Available spaces text', 'ctx-events'),
				type: 'string',
				args: { field: 'availableSpacesLabel' },
			},
			{
				label: __('Available spaces', 'ctx-events'),
				type: 'number',
				args: { field: 'availableSpaces' },
			},
			{
				label: __('Approved bookings', 'ctx-events'),
				type: 'number',
				args: { field: 'approvedBookings' },
			},
			{
				label: __('Pending bookings', 'ctx-events'),
				type: 'number',
				args: { field: 'pendingBookings' },
			},
			{
				label: __('Total capacity', 'ctx-events'),
				type: 'number',
				args: { field: 'totalCapacity' },
			},
			{
				label: __('Booking start', 'ctx-events'),
				type: 'string',
				args: { field: 'bookingStartLabel' },
			},
			{
				label: __('Booking end', 'ctx-events'),
				type: 'string',
				args: { field: 'bookingEndLabel' },
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

			if (field === 'statusLabel') {
				values[attributeName] = event.meta?._booking_enabled
					? __('Bookable', 'ctx-events')
					: __('Booking disabled', 'ctx-events');
			}

			if (field === 'priceLabel') {
				values[attributeName] = getPriceLabel(event, __('Free', 'ctx-events'));
			}

			if (
				field === 'availableSpacesLabel' ||
				field === 'availableSpaces' ||
				field === 'approvedBookings' ||
				field === 'pendingBookings' ||
				field === 'totalCapacity'
			) {
				values[attributeName] = '';
			}

			if (field === 'bookingStartLabel') {
				values[attributeName] = event.meta?._booking_start || '';
			}

			if (field === 'bookingEndLabel') {
				values[attributeName] = event.meta?._booking_end || '';
			}
		});

		return values;
	},
});
