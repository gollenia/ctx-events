import { Modal, SelectControl, TextControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type {
	BookingAttendeeResource,
	BookingDetail,
	BookingFormField,
	PersonName,
} from 'src/types/types';
import DynamicFieldsGrid from './DynamicFieldsGrid';
import {
	type BookingFormValues,
	getBookingFieldInitialValue,
} from './formFields';

type Props = {
	attendee: BookingAttendeeResource | null;
	booking: BookingDetail;
	onClose: () => void;
	onSave: (attendee: BookingAttendeeResource) => void;
};

const createInitialMetadata = (
	fields: BookingFormField[],
	metadata: Record<string, unknown> = {},
	name: PersonName | null = null,
): BookingFormValues => {
	const initialValues: BookingFormValues = {
		...metadata,
		first_name: metadata.first_name ?? name?.firstName ?? '',
		last_name: metadata.last_name ?? name?.lastName ?? '',
	};

	for (const field of fields) {
		if (initialValues[field.name] === undefined) {
			initialValues[field.name] = getBookingFieldInitialValue(field);
		}
	}

	return initialValues;
};

const AttendeeEditModal = ({ attendee, booking, onClose, onSave }: Props) => {
	const [ticketId, setTicketId] = useState<string>(
		attendee?.ticketId ?? booking.availableTickets[0]?.id ?? '',
	);
	const [metadata, setMetadata] = useState<BookingFormValues>(
		createInitialMetadata(
			booking.attendeeForm?.fields ?? [],
			attendee?.metadata ?? {},
			attendee?.name ?? null,
		),
	);

	useEffect(() => {
		setTicketId(attendee?.ticketId ?? booking.availableTickets[0]?.id ?? '');
		setMetadata(
			createInitialMetadata(
				booking.attendeeForm?.fields ?? [],
				attendee?.metadata ?? {},
				attendee?.name ?? null,
			),
		);
	}, [attendee, booking.attendeeForm, booking.availableTickets]);

	const ticketOptions = booking.availableTickets.map((ticket) => ({
		value: ticket.id,
		label: ticket.name,
	}));

	const patchMetadata = (key: string, value: unknown) =>
		setMetadata((prev) => ({ ...prev, [key]: value }));

	const handleSave = () => {
		if (!ticketId) return;

		const firstName =
			typeof metadata.first_name === 'string' ? metadata.first_name.trim() : '';
		const lastName =
			typeof metadata.last_name === 'string' ? metadata.last_name.trim() : '';
		const attendeeName =
			firstName !== '' || lastName !== ''
				? {
						firstName,
						lastName,
						prefix: null,
						suffix: null,
					}
				: null;

		onSave({
			ticketId,
			name: attendeeName,
			metadata,
		});
	};

	return (
		<Modal
			title={
				attendee
					? __('Edit attendee', 'ctx-events')
					: __('Add attendee', 'ctx-events')
			}
			onRequestClose={onClose}
			className="booking-edit__attendee-modal"
			shouldCloseOnClickOutside={false}
		>
			<div className="booking-edit__attendee-modal-body">
				<SelectControl
					label={__('Ticket', 'ctx-events')}
					value={ticketId}
					options={ticketOptions}
					onChange={setTicketId}
				/>

				{booking.attendeeForm.fields.length > 0 && (
					<DynamicFieldsGrid
						fields={booking.attendeeForm.fields}
						values={metadata}
						onChange={patchMetadata}
						gridClassName="booking-edit__attendee-fields"
						fieldClassName="booking-edit__attendee-field"
						inputWrapClassName="booking-edit__field-input-wrap"
					/>
				)}

				<div className="booking-edit__footer booking-edit__attendee-modal-actions">
					<button
						type="button"
						className="components-button is-secondary"
						onClick={onClose}
					>
						{__('Cancel', 'ctx-events')}
					</button>
					<button
						type="button"
						className="components-button is-primary"
						onClick={handleSave}
						disabled={!ticketId}
					>
						{__('Save attendee', 'ctx-events')}
					</button>
				</div>
			</div>
		</Modal>
	);
};

export default AttendeeEditModal;
