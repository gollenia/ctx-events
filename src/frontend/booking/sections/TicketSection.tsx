import { Button } from '../../../shared/__experimentalForm';
import { __ } from '@wordpress/i18n';
import { SectionFooter } from '../components/SectionFooter';
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

			<SectionFooter>
				<Button
					onClick={onNext}
					disabled={totalSelected === 0}
					data-testid="booking-tickets-continue"
				>
					{__('Continue', 'ctx-events')}
				</Button>
			</SectionFooter>
		</div>
	);
}
