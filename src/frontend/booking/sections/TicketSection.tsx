import { __ } from '@wordpress/i18n';
import { TicketCounter } from '../components/TicketCounter';
import type { TicketInfo } from '../types';

type Props = {
	tickets: TicketInfo[];
	counts: Record<string, number>;
	onChange: (ticketId: string, count: number) => void;
	onNext: () => void;
};

export function TicketSection({ tickets, counts, onChange, onNext }: Props) {
	const totalSelected = Object.values(counts).reduce((a, b) => a + b, 0);

	return (
		<div className="booking-section booking-section--tickets">
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
				>
					{__('Continue', 'ctx-events')}
				</button>
			</div>
		</div>
	);
}
