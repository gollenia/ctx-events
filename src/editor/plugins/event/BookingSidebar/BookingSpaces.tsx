import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { isBookingEnabled, type BookingSidebarProps } from './types';

const BookingSpaces = ({ meta, updateMeta }: BookingSidebarProps) => {
	const enabled = isBookingEnabled(meta);

	return (
		<PanelBody title={__('Spaces Settings', 'ctx-events')} initialOpen={true}>
			<TextControl
				label={__('Spaces overall', 'ctx-events')}
				value={String(meta._booking_capacity ?? '')}
				type="number"
				onChange={(value) => {
					updateMeta({ _booking_capacity: value });
				}}
				disabled={!enabled}
			/>

			<TextControl
				label={__('Maximum spaces per booking', 'ctx-events')}
				value={String(meta._max_per_booking ?? '')}
				type="number"
				onChange={(value) => {
					updateMeta({ _max_per_booking: value });
				}}
				disabled={true}
				help={__('Not yet configurable in the editor.', 'ctx-events')}
			/>
		</PanelBody>
	);
};

export default BookingSpaces;
