import { Dialog } from '@contexis/wp-react-form/dialog';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { BookingAccordion } from './BookingAccordion';
import { useBookingData } from './hooks/useBookingData';
import { useSubmitBooking } from './hooks/useSubmitBooking';
import type {
	AttendeePayload,
	BookingPayment,
	BookingState,
	PaymentStateUpdates,
	SectionId,
	SubmitResult,
} from './types';

const BOOKING_OPEN_EVENT = 'ctx:booking:open';

type BookingOpenDetail = {
	postId: number;
};

function initialState(): BookingState {
	return {
		attendees: [],
		registration: {},
		gateway: '',
		couponCode: '',
		couponCheckResult: null,
		openSection: 'tickets',
		completedSections: new Set(),
	};
}

function buildAttendeesFromTickets(
	ticketId: string,
	count: number,
	existingAttendees: AttendeePayload[],
): AttendeePayload[] {
	const normalizedCount = Math.max(0, count);
	const matchingAttendees = existingAttendees.filter(
		(attendee) => attendee.ticket_id === ticketId,
	);
	const nonMatchingAttendees = existingAttendees.filter(
		(attendee) => attendee.ticket_id !== ticketId,
	);
	const nextAttendees = [...nonMatchingAttendees];

	for (let index = 0; index < normalizedCount; index++) {
		nextAttendees.push(
			matchingAttendees[index] ?? { ticket_id: ticketId, metadata: {} },
		);
	}

	return nextAttendees;
}

export default function Booking() {
	const [postId, setPostId] = useState<number | null>(null);
	const [isOpen, setIsOpen] = useState(false);
	const [bookingState, setBookingState] = useState<BookingState>(initialState);
	const [successRef, setSuccessRef] = useState<string | null>(null);
	const [successPayment, setSuccessPayment] = useState<BookingPayment | null>(
		null,
	);
	const [successCustomerEmailStatus, setSuccessCustomerEmailStatus] = useState<
		'sent' | 'failed' | 'skipped' | 'unknown'
	>('unknown');
	const { state: dataState, load } = useBookingData(postId);
	const { status: submitStatus, submit } = useSubmitBooking();

	useEffect(() => {
		const handleOpen = (event: Event) => {
			const customEvent = event as CustomEvent<BookingOpenDetail>;
			const nextPostId = Number(customEvent.detail?.postId ?? 0);

			if (!nextPostId) {
				return;
			}

			setPostId(nextPostId);
			setIsOpen(true);
		};

		document.addEventListener(BOOKING_OPEN_EVENT, handleOpen as EventListener);

		return () => {
			document.removeEventListener(
				BOOKING_OPEN_EVENT,
				handleOpen as EventListener,
			);
		};
	}, []);

	useEffect(() => {
		setBookingState(initialState());
		setSuccessRef(null);
		setSuccessPayment(null);
		setSuccessCustomerEmailStatus('unknown');
	}, [postId]);

	useEffect(() => {
		if (postId && isOpen && dataState.status === 'idle') {
			load();
		}
	}, [postId, isOpen, dataState.status, load]);

	function handleClose() {
		setIsOpen(false);
		setBookingState(initialState());
		setSuccessRef(null);
		setSuccessPayment(null);
		setSuccessCustomerEmailStatus('unknown');
	}

	function handleOpenChange(open: boolean) {
		if (!open) {
			handleClose();
		}
	}

	function handleToggleSection(id: SectionId) {
		setBookingState((prev) => ({ ...prev, openSection: id }));
	}

	function handleTicketChange(ticketId: string, count: number) {
		setBookingState((prev) => ({
			...prev,
			attendees: buildAttendeesFromTickets(ticketId, count, prev.attendees),
		}));
	}

	function handlePaymentStateChange(updates: PaymentStateUpdates) {
		setBookingState((prev) => ({
			...prev,
			...updates,
		}));
	}

	function advanceTo(next: SectionId, completed: SectionId) {
		setBookingState((prev) => {
			const completedSections = new Set(prev.completedSections);
			completedSections.add(completed);
			return { ...prev, openSection: next, completedSections };
		});
	}

	function handleTicketsDone() {
		const data = dataState.status === 'loaded' ? dataState.data : null;
		if (!data) return;

		const hasAttendees =
			data.attendeeForm !== null && data.attendeeForm.fields.length > 0;
		advanceTo(hasAttendees ? 'attendees' : 'booking', 'tickets');
	}

	function handleAttendeesDone(attendees: AttendeePayload[]) {
		setBookingState((prev) => {
			const completedSections = new Set(prev.completedSections);
			completedSections.add('attendees');
			return {
				...prev,
				attendees,
				openSection: 'booking',
				completedSections,
			};
		});
	}

	function handleRegistrationDone(registration: Record<string, unknown>) {
		setBookingState((prev) => {
			const completedSections = new Set(prev.completedSections);
			completedSections.add('booking');
			return {
				...prev,
				registration,
				openSection: 'payment',
				completedSections,
			};
		});
	}

	function handleResult(result: SubmitResult) {
		if (result.type === 'success') {
			setSuccessRef(result.reference);
			setSuccessPayment(result.payment);
			setSuccessCustomerEmailStatus(result.customerEmailStatus);
		}
	}

	if (!postId) return null;

	return (
		<Dialog open={isOpen} onOpenChange={handleOpenChange}>
			<Dialog.Portal>
				<Dialog.Backdrop
					className="booking-modal__backdrop"
					data-testid="booking-modal"
				/>
				<Dialog.Panel
					variant="fullscreen"
					className="booking-modal__dialog"
					aria-label={__('Book event', 'ctx-events')}
					data-testid="booking-modal"
				>
					<Dialog.Header className="booking-modal__header">
						<Dialog.Title className="booking-modal__title">
							{dataState.status === 'loaded'
								? dataState.data.eventName
								: __('Book event', 'ctx-events')}
						</Dialog.Title>
						<Dialog.Close
							className="booking-modal__close"
							aria-label={__('Close', 'ctx-events')}
						>
							<svg
								aria-hidden="true"
								focusable="false"
								xmlns="http://www.w3.org/2000/svg"
								width="24"
								height="24"
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								strokeWidth="2"
								strokeLinecap="round"
								strokeLinejoin="round"
							>
								<line x1="18" y1="6" x2="6" y2="18" />
								<line x1="6" y1="6" x2="18" y2="18" />
							</svg>
						</Dialog.Close>
					</Dialog.Header>
					<div className="booking-modal__body">
						{dataState.status === 'idle' || dataState.status === 'loading' ? (
							<p className="booking-loading">{__('Loading…', 'ctx-events')}</p>
						) : dataState.status === 'error' ? (
							<p className="booking-error" role="alert">
								{dataState.message}
							</p>
						) : (
							<BookingAccordion
								data={dataState.data}
								state={bookingState}
								postId={postId}
								isSubmitting={submitStatus === 'loading'}
								successRef={successRef}
								successPayment={successPayment}
								successCustomerEmailStatus={successCustomerEmailStatus}
								onTicketChange={handleTicketChange}
								onTicketsDone={handleTicketsDone}
								onAttendeesDone={handleAttendeesDone}
								onRegistrationDone={handleRegistrationDone}
								onSubmit={submit}
								onResult={handleResult}
								onPaymentStateChange={handlePaymentStateChange}
								onToggleSection={handleToggleSection}
								onClose={handleClose}
							/>
						)}
					</div>
				</Dialog.Panel>
			</Dialog.Portal>
		</Dialog>
	);
}
