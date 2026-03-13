import { __ } from '@wordpress/i18n';
import { AccordionSection } from './AccordionSection';
import { AttendeeSection } from './sections/AttendeeSection';
import { BookingFormSection } from './sections/BookingFormSection';
import { PaymentSection } from './sections/PaymentSection';
import { SuccessSection } from './sections/SuccessSection';
import { TicketSection } from './sections/TicketSection';
import type {
	AttendeePayload,
	BookingData,
	BookingPayment,
	BookingState,
	SectionId,
	SubmitResult,
} from './types';

type Props = {
	data: BookingData;
	state: BookingState;
	postId: number;
	isSubmitting: boolean;
	successRef: string | null;
	successPayment: BookingPayment | null;
	onTicketChange: (ticketId: string, count: number) => void;
	onTicketsDone: () => void;
	onAttendeesDone: (attendees: AttendeePayload[]) => void;
	onRegistrationDone: (registration: Record<string, unknown>) => void;
	onSubmit: (payload: {
		token: string;
		event_id: number;
		registration: Record<string, unknown>;
		attendees: AttendeePayload[];
		gateway: string;
		coupon_code?: string;
	}) => Promise<SubmitResult>;
	onResult: (result: SubmitResult) => void;
	onToggleSection: (id: SectionId) => void;
	onClose: () => void;
};

function hasAttendees(data: BookingData): boolean {
	return data.attendeeForm !== null && data.attendeeForm.fields.length > 0;
}

function getSections(data: BookingData): SectionId[] {
	const base: SectionId[] = ['tickets'];
	if (hasAttendees(data)) base.push('attendees');
	base.push('booking', 'payment');
	return base;
}

export function BookingAccordion({
	data,
	state,
	postId,
	isSubmitting,
	successRef,
	successPayment,
	onTicketChange,
	onTicketsDone,
	onAttendeesDone,
	onRegistrationDone,
	onSubmit,
	onResult,
	onToggleSection,
	onClose,
}: Props) {
	if (successRef !== null) {
		return (
			<SuccessSection
				reference={successRef}
				eventName={data.eventName}
				payment={successPayment}
				onClose={onClose}
			/>
		);
	}
	const sections = getSections(data);
	const totalSelected = Object.values(state.tickets).reduce((a, b) => a + b, 0);

	function isSectionDisabled(id: SectionId): boolean {
		const idx = sections.indexOf(id);
		if (idx <= 0) return false;
		const prev = sections[idx - 1];
		return !state.completedSections.has(prev);
	}

	return (
		<div className="booking-accordion">
			<AccordionSection
				id="tickets"
				title={__('Tickets', 'ctx-events')}
				isOpen={state.openSection === 'tickets'}
				isCompleted={state.completedSections.has('tickets')}
				onToggle={onToggleSection}
			>
				<TicketSection
					tickets={data.tickets}
					counts={state.tickets}
					onChange={onTicketChange}
					onNext={onTicketsDone}
				/>
			</AccordionSection>

			{hasAttendees(data) && (
				<AccordionSection
					id="attendees"
					title={__('Attendees', 'ctx-events')}
					isOpen={state.openSection === 'attendees'}
					isCompleted={state.completedSections.has('attendees')}
					isDisabled={isSectionDisabled('attendees')}
					onToggle={onToggleSection}
				>
					<AttendeeSection
						attendeeForm={data.attendeeForm!}
						tickets={data.tickets}
						ticketCounts={state.tickets}
						initialAttendees={state.attendees}
						onNext={onAttendeesDone}
					/>
				</AccordionSection>
			)}

			<AccordionSection
				id="booking"
				title={__('Your details', 'ctx-events')}
				isOpen={state.openSection === 'booking'}
				isCompleted={state.completedSections.has('booking')}
				isDisabled={isSectionDisabled('booking')}
				onToggle={onToggleSection}
			>
				<BookingFormSection
					bookingForm={data.bookingForm}
					initialData={state.registration}
					onNext={onRegistrationDone}
				/>
			</AccordionSection>

			<AccordionSection
				id="payment"
				title={
					totalSelected > 0 &&
					data.tickets.some(
						(t) => (state.tickets[t.id] ?? 0) > 0 && t.price_in_cents > 0,
					)
						? __('Payment', 'ctx-events')
						: __('Confirm booking', 'ctx-events')
				}
				isOpen={state.openSection === 'payment'}
				isCompleted={state.completedSections.has('payment')}
				isDisabled={isSectionDisabled('payment')}
				onToggle={onToggleSection}
			>
				<PaymentSection
					data={data}
					bookingState={state}
					onResult={onResult}
					onSubmit={onSubmit}
					postId={postId}
					isSubmitting={isSubmitting}
				/>
			</AccordionSection>
		</div>
	);
}
