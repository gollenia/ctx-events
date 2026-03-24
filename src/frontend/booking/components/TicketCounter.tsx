import { formatPrice } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import type { TicketInfo } from '../types';

type Props = {
	ticket: TicketInfo;
	count: number;
	onChange: (count: number) => void;
};

export function TicketCounter({ ticket, count, onChange }: Props) {
	const max = ticket.booking_limit ?? ticket.ticket_limit_per_booking ?? 10;

	function decrement() {
		onChange(Math.max(0, count - 1));
	}

	function increment() {
		onChange(Math.min(max, count + 1));
	}

	return (
		<div className="booking-ticket">
			<div className="booking-ticket__info">
				<span className="booking-ticket__name">{ticket.name}</span>
				<span className="booking-ticket__price">
					{ticket.price.amountCents === 0
						? __('Free', 'ctx-events')
						: formatPrice({
								amountCents: ticket.price.amountCents,
								currency: ticket.price.currency,
							})}
				</span>
			</div>
			<div className="booking-ticket__counter">
				<button
					type="button"
					className="booking-ticket__btn"
					onClick={decrement}
					disabled={count === 0}
					aria-label={__('Remove one', 'ctx-events')}
				>
					–
				</button>
				<span className="booking-ticket__count" aria-live="polite">
					{count}
				</span>
				<button
					type="button"
					className="booking-ticket__btn"
					onClick={increment}
					disabled={count >= max}
					aria-label={__('Add one', 'ctx-events')}
				>
					+
				</button>
			</div>
		</div>
	);
}
