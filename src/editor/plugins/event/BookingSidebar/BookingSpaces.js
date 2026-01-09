import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const BookingSpaces = (props) => {
	const { meta, setMeta } = props;
	return (
		<PanelBody title={__('Spaces Settings', 'events')} initialOpen={true}>
			<TextControl
				label={__('Spaces overall', 'events')}
				value={meta._booking_capacity}
				type="number"
				onChange={(value) => {
					setMeta({ _booking_capacity: value });
				}}
				disabled={!meta._booking_enabled}
			/>

			<TextControl
				label={__('Maximum spaces per booking', 'events')}
				value={meta._max_per_booking}
				type="number"
				onChange={(value) => {
					setMeta({ _max_per_booking: value });
				}}
				disabled={true}
			/>
		</PanelBody>
	);
};

export default BookingSpaces;
