import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { FormFieldRenderer } from '../components/FormFieldRenderer';
import { buildInitialFormValues } from '../formFields';
import { isFieldVisible } from '../hooks/useFieldVisibility';
import type { AttendeePayload, BookingFormData, TicketInfo } from '../types';

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
	ticketCounts: Record<string, number>;
	initialAttendees: AttendeePayload[];
	onNext: (attendees: AttendeePayload[]) => void;
};

function buildInitialEntries(
	tickets: TicketInfo[],
	counts: Record<string, number>,
	form: BookingFormData,
	initial: AttendeePayload[],
): AttendeeEntry[] {
	const entries: AttendeeEntry[] = [];
	let globalIndex = 0;

	for (const ticket of tickets) {
		const count = counts[ticket.id] ?? 0;
		for (let i = 0; i < count; i++) {
			const existing = initial[globalIndex];
				entries.push({
					ticketId: ticket.id,
					ticketName: ticket.name,
					index: globalIndex,
					formData: existing
						? buildInitialFormValues(form.fields, existing.metadata)
						: buildInitialFormValues(form.fields, {}),
					errors: {},
				});
			globalIndex++;
		}
	}

	return entries;
}

function validateEntry(
	entry: AttendeeEntry,
	form: BookingFormData,
	formData: Record<string, unknown>,
): Record<string, string> {
	const errors: Record<string, string> = {};

	for (const f of form.fields) {
		if (!isFieldVisible(f.visibilityRule, formData)) continue;
		if (!f.required) continue;

		const val = formData[f.name];
		const isEmpty =
			val === undefined || val === null || val === '' || val === false;
		if (isEmpty) {
			errors[f.name] = __('This field is required.', 'ctx-events');
		}
	}

	return errors;
}

export function AttendeeSection({
	attendeeForm,
	tickets,
	ticketCounts,
	initialAttendees,
	onNext,
}: Props) {
	const [entries, setEntries] = useState<AttendeeEntry[]>(() =>
		buildInitialEntries(
			tickets,
			ticketCounts,
			attendeeForm,
			initialAttendees,
		),
	);

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

	function handleSubmit() {
		let hasErrors = false;
		const updated = entries.map((e) => {
			const errs = validateEntry(e, attendeeForm, e.formData);
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
					<h3 className="booking-attendee__title">
						{entry.ticketName} #{entry.index + 1}
					</h3>
					<FormFieldRenderer
						fields={attendeeForm.fields}
						formData={entry.formData}
						errors={entry.errors}
						onChange={(name, value) => handleChange(entry.index, name, value)}
					/>
				</div>
			))}

			<div className="booking-section__footer">
				<button
					type="button"
					className="booking-btn booking-btn--primary"
					onClick={handleSubmit}
					data-testid="booking-attendees-continue"
				>
					{__('Continue', 'ctx-events')}
				</button>
			</div>
		</div>
	);
}
