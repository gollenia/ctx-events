import PanelTitle from '@events/adminfields/PanelTitle';
import { Flex, PanelBody, SelectControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import icons from '../icons';
import {
	type BookingFormOption,
	type BookingSidebarProps,
	isBookingEnabled,
} from './types';

type EntityRecord = {
	id: number;
	title?: {
		raw?: string;
	};
};

const emptyOption: BookingFormOption = { value: 0, label: '' };

const useFormOptions = (postType: string): BookingFormOption[] => {
	return useSelect(
		(select) => {
			const { getEntityRecords } = select(coreStore);
			const records = (getEntityRecords('postType', postType, {
				per_page: -1,
			}) ?? []) as EntityRecord[];

			return [
				emptyOption,
				...records.map((record) => ({
					value: record.id,
					label: record.title?.raw ?? '',
				})),
			];
		},
		[postType],
	);
};

const BookingForms = ({ meta, updateMeta }: BookingSidebarProps) => {
	const bookingFormList = useFormOptions('ctx-booking-form');
	const attendeeFormList = useFormOptions('ctx-attendee-form');
	const enabled = isBookingEnabled(meta);

	return (
		<PanelBody
			title={
				<PanelTitle
					icon={icons.form}
					title={__('Booking Forms', 'ctx-events')}
				/>
			}
			initialOpen={true}
			className="events-booking-settings"
		>
			<Flex gap={4} direction="column">
				<SelectControl
					label={__('Registration Form', 'ctx-events')}
					value={String(meta._booking_form ?? 0)}
					onChange={(value) => {
						updateMeta({ _booking_form: value });
					}}
					disabled={!enabled}
					options={bookingFormList}
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>

				<SelectControl
					label={__('Attendee Form', 'ctx-events')}
					value={String(meta._attendee_form ?? 0)}
					onChange={(value) => {
						updateMeta({ _attendee_form: value });
					}}
					disabled={!enabled}
					options={attendeeFormList}
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			</Flex>
		</PanelBody>
	);
};

export default BookingForms;
