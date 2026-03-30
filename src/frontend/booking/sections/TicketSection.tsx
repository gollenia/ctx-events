import { __ } from '@wordpress/i18n';
import { TicketCounter } from '../components/TicketCounter';
import type { AttendeePayload, TicketInfo } from '../types';

type Props = {
	tickets: TicketInfo[];
	attendees: AttendeePayload[];
	onChange: (ticketId: string, count: number) => void;
	onNext: () => void;
};

export function TicketSection({ tickets, attendees, onChange, onNext }: Props) {
	const counts = attendees.reduce<Record<string, number>>((result, attendee) => {
		result[attendee.ticket_id] = (result[attendee.ticket_id] ?? 0) + 1;
		return result;
	}, {});
	const totalSelected = attendees.length;

	return (
		<div
			className="booking-section booking-section--tickets"
			data-testid="booking-section-tickets"
		>
			<div className="booking-ticket-list">
				{tickets.map((ticket) => (
					<TicketCounter
						key={ticket.id}
						ticket={ticket}
						count={counts[ticket.id] ?? 0}
						onChange={(count) => onChange(ticket.id, count)}
					/>
				))}
			</div>

			<div className="booking-section__footer">
				<button
					type="button"
					className="booking-btn booking-btn--primary"
					onClick={onNext}
					disabled={totalSelected === 0}
					data-testid="booking-tickets-continue"
				>
					{__('Continue', 'ctx-events')}
				</button>
			</div>
		</div>
	);
}
