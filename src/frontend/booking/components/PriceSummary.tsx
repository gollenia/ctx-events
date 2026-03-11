import { formatPrice } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import type { TicketInfo } from '../types';

type Props = {
	tickets: TicketInfo[];
	ticketCounts: Record<string, number>;
};

export function PriceSummary({ tickets, ticketCounts }: Props) {
	const lines = tickets.filter((t) => (ticketCounts[t.id] ?? 0) > 0);

	if (lines.length === 0) return null;

	const currency = lines[0]?.currency ?? 'EUR';
	const total = lines.reduce(
		(sum, t) => sum + t.price_in_cents * (ticketCounts[t.id] ?? 0),
		0,
	);

	return (
		<div className="booking-price-summary">
			{lines.map((ticket) => {
				const count = ticketCounts[ticket.id] ?? 0;
				return (
					<div key={ticket.id} className="booking-price-summary__line">
						<span className="booking-price-summary__label">
							{count}× {ticket.name}
						</span>
						<span className="booking-price-summary__amount">
							{ticket.price_in_cents === 0
								? __('Free', 'ctx-events')
								: formatPrice({
										amountCents: ticket.price_in_cents * count,
										currency,
									})}
						</span>
					</div>
				);
			})}
			<div className="booking-price-summary__total">
				<span className="booking-price-summary__label">
					{__('Total', 'ctx-events')}
				</span>
				<span className="booking-price-summary__amount">
					{total === 0
						? __('Free', 'ctx-events')
						: formatPrice(total, currency)}
				</span>
			</div>
		</div>
	);
}
