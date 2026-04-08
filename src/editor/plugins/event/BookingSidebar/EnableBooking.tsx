import {
	CheckboxControl,
	Flex,
	PanelBody,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import DateTimeFieldRow from '../DateTimeFieldRow';
import {
	type BookingSidebarProps,
	getEventEditorLocalization,
	isBookingEnabled,
} from './types';

const splitDateTime = (dateTime?: string) => {
	if (!dateTime) {
		return { date: '', time: '' };
	}

	const [date, time] = dateTime.split('T');

	return { date, time: time ? time.slice(0, 5) : '00:00' };
};

const combineDateTime = (date: string, time: string) => {
	if (!date) {
		return '';
	}

	return `${date}T${time || '00:00'}`;
};

const EnableBooking = ({ meta, updateMeta }: BookingSidebarProps) => {
	const localization = getEventEditorLocalization();
	const globallyEnabled = localization.bookingEnabled !== false;
	const enabled = isBookingEnabled(meta);
	const bookingStart = splitDateTime(meta._booking_start);
	const bookingEnd = splitDateTime(meta._booking_end);

	return (
		<PanelBody title={__('Booking Settings', 'ctx-events')} initialOpen={true}>
			<Flex gap={4} direction="column">
				<CheckboxControl
					label={__('Enable Bookings', 'ctx-events')}
					checked={enabled}
					onChange={(value) => {
						updateMeta({ _booking_enabled: value });
					}}
					disabled={!globallyEnabled}
					__nextHasNoMarginBottom
				/>

				<DateTimeFieldRow
					label={__('Booking Start', 'ctx-events')}
					date={bookingStart.date}
					time={bookingStart.time}
					showTime={true}
					disabled={!enabled}
					onDateChange={(value) => {
						updateMeta({
							_booking_start: combineDateTime(value, bookingStart.time),
						});
					}}
					onTimeChange={(value) => {
						updateMeta({
							_booking_start: combineDateTime(bookingStart.date, value),
						});
					}}
				/>

				<DateTimeFieldRow
					label={__('Booking End', 'ctx-events')}
					date={bookingEnd.date}
					time={bookingEnd.time}
					showTime={true}
					disabled={!enabled}
					minDate={bookingStart.date}
					minTime={
						bookingStart.date && bookingEnd.date === bookingStart.date
							? bookingStart.time
							: undefined
					}
					onDateChange={(value) => {
						updateMeta({
							_booking_end: combineDateTime(value, bookingEnd.time),
						});
					}}
					onTimeChange={(value) => {
						updateMeta({
							_booking_end: combineDateTime(bookingEnd.date, value),
						});
					}}
				/>
			</Flex>
		</PanelBody>
	);
};

export default EnableBooking;
