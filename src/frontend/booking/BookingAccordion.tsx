import { Accordion } from '@base-ui/react/accordion';
import { __ } from '@wordpress/i18n';
import { AccordionSection } from './AccordionSection';
import { BookingSummarySlot } from './components/BookingSummarySlot';
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
	PaymentStateUpdates,
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
	successCustomerEmailStatus: 'sent' | 'failed' | 'skipped' | 'unknown';
	onTicketChange: (ticketId: string, count: number) => void;
	onTicketsDone: () => void;
	onAttendeesDone: (attendees: AttendeePayload[]) => void;
	onAttendeeRemove: (index: number) => void;
	onRegistrationDone: (registration: Record<string, unknown>) => void;
	onSubmit: (payload: {
		token: string;
		event_id: number;
		registration: Record<string, unknown>;
		attendees: AttendeePayload[];
		gateway: string;
		coupon_code?: string;
		donation_amount?: number;
	}) => Promise<SubmitResult>;
	onResult: (result: SubmitResult) => void;
	onPaymentStateChange: (updates: PaymentStateUpdates) => void;
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

function isSectionDisabled(
	sections: SectionId[],
	completedSections: Set<SectionId>,
	id: SectionId,
): boolean {
	const idx = sections.indexOf(id);
	if (idx <= 0) return false;
	const prev = sections[idx - 1];
	return !completedSections.has(prev);
}

function getPaymentSectionTitle(data: BookingData, state: BookingState): string {
	const hasPaidTickets = data.tickets.some(
		(ticket) =>
			ticket.price.amountCents > 0 &&
			state.attendees.some((attendee) => attendee.ticket_id === ticket.id),
	);

	return hasPaidTickets
		? __('Payment', 'ctx-events')
		: __('Confirm booking', 'ctx-events');
}

export function BookingAccordion({
	data,
	state,
	postId,
	isSubmitting,
	successRef,
	successPayment,
	successCustomerEmailStatus,
	onTicketChange,
	onTicketsDone,
	onAttendeesDone,
	onAttendeeRemove,
	onRegistrationDone,
	onSubmit,
	onResult,
	onPaymentStateChange,
	onToggleSection,
	onClose,
}: Props) {
	if (successRef !== null) {
		return (
			<SuccessSection
				reference={successRef}
				eventName={data.eventName}
				payment={successPayment}
				customerEmailStatus={successCustomerEmailStatus}
				onClose={onClose}
			/>
		);
	}
	const sections = getSections(data);
	const paymentSectionDisabled = isSectionDisabled(
		sections,
		state.completedSections,
		'payment',
	);
	const attendeesSectionDisabled = isSectionDisabled(
		sections,
		state.completedSections,
		'attendees',
	);
	const bookingSectionDisabled = isSectionDisabled(
		sections,
		state.completedSections,
		'booking',
	);
	const paymentSectionTitle = getPaymentSectionTitle(data, state);

	return (
		<div className="booking-layout">
			<div className="booking-layout__main">
				<Accordion.Root
					className="booking-accordion"
					value={[state.openSection]}
					onValueChange={(value) => {
						const [nextSection] = value;
						if (nextSection) {
							onToggleSection(nextSection as SectionId);
						}
					}}
				>
					<AccordionSection
						id="tickets"
						title={__('Tickets', 'ctx-events')}
						isCompleted={state.completedSections.has('tickets')}
					>
						<TicketSection
							tickets={data.tickets}
							attendees={state.attendees}
							onChange={onTicketChange}
							onNext={onTicketsDone}
						/>
					</AccordionSection>

					{hasAttendees(data) && (
						<AccordionSection
							id="attendees"
							title={__('Attendees', 'ctx-events')}
							isCompleted={state.completedSections.has('attendees')}
							isDisabled={attendeesSectionDisabled}
						>
							<AttendeeSection
								attendeeForm={data.attendeeForm!}
								tickets={data.tickets}
								initialAttendees={state.attendees}
								onRemove={onAttendeeRemove}
								onNext={onAttendeesDone}
							/>
						</AccordionSection>
					)}

					<AccordionSection
						id="booking"
						title={__('Your details', 'ctx-events')}
						isCompleted={state.completedSections.has('booking')}
						isDisabled={bookingSectionDisabled}
					>
						<BookingFormSection
							bookingForm={data.bookingForm}
							initialData={state.registration}
							onNext={onRegistrationDone}
						/>
					</AccordionSection>
					<BookingSummarySlot
						data={data}
						state={state}
						postId={postId}
						onPaymentStateChange={onPaymentStateChange}
						visible={!paymentSectionDisabled}
						className="booking-summary-panel--mobile"
						couponClassName="booking-coupon-slot"
					/>
					<AccordionSection
						id="payment"
						title={paymentSectionTitle}
						isCompleted={state.completedSections.has('payment')}
						isDisabled={paymentSectionDisabled}
					>
						<PaymentSection
							data={data}
							bookingState={state}
							onResult={onResult}
							onPaymentStateChange={onPaymentStateChange}
							onSubmit={onSubmit}
							postId={postId}
							isSubmitting={isSubmitting}
						/>
					</AccordionSection>
				</Accordion.Root>
			</div>

			<BookingSummarySlot
				data={data}
				state={state}
				postId={postId}
				onPaymentStateChange={onPaymentStateChange}
				className="booking-summary-panel--desktop"
				couponClassName="booking-coupon-slot booking-coupon-slot--desktop"
			/>
		</div>
	);
}
