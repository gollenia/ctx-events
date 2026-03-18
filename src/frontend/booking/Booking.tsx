import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { BookingAccordion } from './BookingAccordion';
import { useBookingData } from './hooks/useBookingData';
import { useSubmitBooking } from './hooks/useSubmitBooking';
import type {
	AttendeePayload,
	BookingPayment,
	BookingState,
	SectionId,
	SubmitResult,
} from './types';

const BOOKING_OPEN_EVENT = 'ctx:booking:open';

type BookingOpenDetail = {
	postId: number;
};

function initialState(): BookingState {
	return {
		tickets: {},
		attendees: [],
		registration: {},
		gateway: '',
		couponCode: '',
		openSection: 'tickets',
		completedSections: new Set(),
	};
}

export default function Booking() {
	const [postId, setPostId] = useState<number | null>(null);
	const [isOpen, setIsOpen] = useState(false);
	const [bookingState, setBookingState] = useState<BookingState>(initialState);
	const [successRef, setSuccessRef] = useState<string | null>(null);
	const [successPayment, setSuccessPayment] = useState<BookingPayment | null>(null);
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
	}

	function handleToggleSection(id: SectionId) {
		setBookingState((prev) => ({ ...prev, openSection: id }));
	}

	function handleTicketChange(ticketId: string, count: number) {
		setBookingState((prev) => ({
			...prev,
			tickets: { ...prev.tickets, [ticketId]: count },
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
			return { ...prev, attendees, openSection: 'booking', completedSections };
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
		}
	}

	if (!postId || !isOpen) return null;

	return (
		<div
			className="booking-modal"
			role="dialog"
			aria-modal="true"
			aria-label={__('Book event', 'ctx-events')}
		>
			<div className="booking-modal__backdrop" onClick={handleClose} />
			<div className="booking-modal__dialog">
				<div className="booking-modal__header">
					{dataState.status === 'loaded' && (
						<h2 className="booking-modal__title">{dataState.data.eventName}</h2>
					)}
					<button
						type="button"
						className="booking-modal__close"
						onClick={handleClose}
						aria-label={__('Close', 'ctx-events')}
					>
						×
					</button>
				</div>

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
							onTicketChange={handleTicketChange}
							onTicketsDone={handleTicketsDone}
							onAttendeesDone={handleAttendeesDone}
							onRegistrationDone={handleRegistrationDone}
							onSubmit={submit}
							onResult={handleResult}
							onToggleSection={handleToggleSection}
							onClose={handleClose}
						/>
					)}
				</div>
			</div>
		</div>
	);
}
