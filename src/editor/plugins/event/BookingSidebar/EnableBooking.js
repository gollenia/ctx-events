import { CheckboxControl, PanelBody, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const EnableBooking = (props) => {
	const { meta, setMeta } = props;
	const [showTickets, setShowTickets] = useState(false);
	console.log('EnableBooking', meta);
	return (
		<PanelBody title={__('Booking Settings', 'ctx-events')} initialOpen={true}>
			{!window?.eventEditorLocalization?.bookingEnabled ? (
				<div className="inspector-message inspector-message--error">
					<div>
						<b>{__('Bookings are globally disabled', 'ctx-events')}</b>
					</div>
					<span>
						{window?.eventEditorLocalization?.bookingMessage}
					</span>
				</div>
			) : null}

			<CheckboxControl
				label={__('Enable Bookings', 'ctx-events')}
				checked={meta._booking_enabled}
				onChange={(value) => {
					setMeta({ _booking_enabled: value });
				}}
				disabled={
					!window?.eventEditorLocalization?.bookingEnabled 
				}
			/>

			<TextControl
				label={__('Booking Start Date', 'ctx-events')}
				value={meta._booking_start}
				type="datetime-local"
				onChange={(value) => {
					setMeta({ _booking_start: value });
				}}
				disabled={!meta._booking_enabled}
			/>

			<TextControl
				label={__('Booking End Date', 'ctx-events')}
				value={meta._booking_end}
				type="datetime-local"
				onChange={(value) => {
					setMeta({ _booking_end: value });
				}}
				disabled={!meta._booking_enabled}
			/>
		</PanelBody>
	);
};

export default EnableBooking;
