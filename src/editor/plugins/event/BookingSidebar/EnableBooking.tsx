import { CheckboxControl, PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import {
	getEventEditorLocalization,
	isBookingEnabled,
	type BookingSidebarProps,
} from './types';

const EnableBooking = ({ meta, updateMeta }: BookingSidebarProps) => {
	const localization = getEventEditorLocalization();
	const globallyEnabled = localization.bookingEnabled !== false;
	const enabled = isBookingEnabled(meta);

	return (
		<PanelBody title={__('Booking Settings', 'ctx-events')} initialOpen={true}>
			{!globallyEnabled ? (
				<div className="inspector-message inspector-message--error">
					<div>
						<b>{__('Bookings are globally disabled', 'ctx-events')}</b>
					</div>
					<span>{localization.bookingMessage}</span>
				</div>
			) : null}

			<CheckboxControl
				label={__('Enable Bookings', 'ctx-events')}
				checked={enabled}
				onChange={(value) => {
					updateMeta({ _booking_enabled: value });
				}}
				disabled={!globallyEnabled}
			/>

			<TextControl
				label={__('Booking Start Date', 'ctx-events')}
				value={meta._booking_start ?? ''}
				type="datetime-local"
				onChange={(value) => {
					updateMeta({ _booking_start: value });
				}}
				disabled={!enabled}
			/>

			<TextControl
				label={__('Booking End Date', 'ctx-events')}
				value={meta._booking_end ?? ''}
				type="datetime-local"
				onChange={(value) => {
					updateMeta({ _booking_end: value });
				}}
				disabled={!enabled}
			/>
		</PanelBody>
	);
};

export default EnableBooking;
