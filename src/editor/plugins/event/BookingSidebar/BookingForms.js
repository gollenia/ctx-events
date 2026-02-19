import { Button, PanelBody, SelectControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import TicketModal from './TicketModal';

const SelectBookingForms = (props) => {
	const { meta, setMeta } = props;
	const [showTickets, setShowTickets] = useState(false);
	const bookingFormList = useSelect((select) => {
		const { getEntityRecords } = select(coreStore);
		const query = { per_page: -1 };
		const list = getEntityRecords('postType', 'ctx-booking-form', query);

		const formsArray = [{ value: 0, label: '' }];
		if (!list) {
			return formsArray;
		}

		list.map((form) => {
			formsArray.push({ value: form.id, label: form.title.raw });
		});

		return formsArray;
	}, []);

	const attendeeFormList = useSelect((select) => {
		const { getEntityRecords } = select(coreStore);
		const query = { per_page: -1 };
		const list = getEntityRecords('postType', 'ctx-attendee-form', query);

		const formsArray = [{ value: 0, label: '' }];
		if (!list) {
			return formsArray;
		}

		list.map((form) => {
			formsArray.push({ value: form.id, label: form.title.raw });
		});

		return formsArray;
	}, []);

	return (
		<PanelBody
			title={__('Booking Forms', 'ctx-events')}
			initialOpen={true}
			className="events-booking-settings"
		>
			<SelectControl
				label={__('Registration Form', 'ctx-events')}
				value={meta._booking_form}
				onChange={(value) => {
					setMeta({ _booking_form: value });
				}}
				disabled={!meta._booking_enabled}
				options={bookingFormList}
				disableCustomColors={true}
			/>

			<SelectControl
				label={__('Attendee Form', 'ctx-events')}
				value={meta._attendee_form}
				onChange={(value) => {
					setMeta({ _attendee_form: value });
				}}
				disabled={!meta._booking_enabled}
				options={attendeeFormList}
				disableCustomColors={true}
			/>

			
		</PanelBody>
	);
};

export default SelectBookingForms;
