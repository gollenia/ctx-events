import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@contexis/wp-react-form';
import EventIcon from '../../../shared/icons/EventIcon';
import { FormFieldRenderer } from '../components/FormFieldRenderer';
import { SectionFooter } from '../components/SectionFooter';
import { buildInitialFormValues } from '../formFields';
import type { AttendeePayload, BookingFormData, TicketInfo } from '../types';
import { isBookingFormComplete, validateBookingFormData } from '../validation';

type AttendeeEntry = {
	ticketId: string;
	ticketName: string;
	index: number;
	formData: Record<string, unknown>;
	errors: Record<string, string>;
};

type Props = {
	attendeeForm: BookingFormData;
	tickets: TicketInfo[];
	initialAttendees: AttendeePayload[];
	onRemove: (index: number) => void;
	onNext: (attendees: AttendeePayload[]) => void;
};

function buildInitialEntries(
	tickets: TicketInfo[],
	form: BookingFormData,
	initial: AttendeePayload[],
): AttendeeEntry[] {
	const entries: AttendeeEntry[] = [];

	initial.forEach((existing, index) => {
		const ticket = tickets.find((item) => item.id === existing.ticket_id);

		if (!ticket) {
			return;
		}

		entries.push({
			ticketId: ticket.id,
			ticketName: ticket.name,
			index,
			formData: buildInitialFormValues(form.fields, existing.metadata),
			errors: {},
		});
	});

	return entries;
}

export function AttendeeSection({
	attendeeForm,
	tickets,
	initialAttendees,
	onRemove,
	onNext,
}: Props) {
	const [entries, setEntries] = useState<AttendeeEntry[]>(() =>
		buildInitialEntries(tickets, attendeeForm, initialAttendees),
	);

	useEffect(() => {
		setEntries(buildInitialEntries(tickets, attendeeForm, initialAttendees));
	}, [tickets, attendeeForm, initialAttendees]);

	function handleChange(index: number, name: string, value: unknown) {
		setEntries((prev) =>
			prev.map((e) =>
				e.index === index
					? {
							...e,
							formData: { ...e.formData, [name]: value },
							errors: { ...e.errors, [name]: '' },
						}
					: e,
			),
			);
	}

	function handleRemove(index: number) {
		setEntries((prev) => prev.filter((entry) => entry.index !== index));
		onRemove(index);
	}

	function handleSubmit() {
		let hasErrors = false;
		const updated = entries.map((e) => {
			const errs = validateBookingFormData(attendeeForm, e.formData);
			if (Object.keys(errs).length > 0) hasErrors = true;
			return { ...e, errors: errs };
		});

		setEntries(updated);
		if (hasErrors) return;

		const payloads: AttendeePayload[] = entries.map((e) => ({
			ticket_id: e.ticketId,
			metadata: e.formData,
		}));

		onNext(payloads);
	}

	const canContinue = entries.every(
		(entry) => isBookingFormComplete(attendeeForm, entry.formData),
	);

	return (
		<div
			className="booking-section booking-section--attendees"
			data-testid="booking-section-attendees"
		>
				{entries.map((entry) => (
					<div
						key={entry.index}
						className="booking-attendee"
						data-testid={`booking-attendee-${entry.index}`}
					>
						<div className="booking-attendee__header">
							<h3 className="booking-attendee__title">
								{entry.ticketName} #{entry.index + 1}
							</h3>
							<button
								type="button"
								className="booking-attendee__remove"
								onClick={() => handleRemove(entry.index)}
								data-testid={`booking-attendee-remove-${entry.index}`}
								aria-label={__('Remove attendee', 'ctx-events')}
								title={__('Remove attendee', 'ctx-events')}
							>
								<EventIcon name="delete" />
							</button>
						</div>
						<FormFieldRenderer
							fields={attendeeForm.fields}
							formData={entry.formData}
						errors={entry.errors}
						onChange={(name, value) => handleChange(entry.index, name, value)}
					/>
				</div>
			))}

			<SectionFooter>
				<Button
					onClick={handleSubmit}
					disabled={!canContinue}
					data-testid="booking-attendees-continue"
				>
					{__('Continue', 'ctx-events')}
				</Button>
			</SectionFooter>
		</div>
	);
}
