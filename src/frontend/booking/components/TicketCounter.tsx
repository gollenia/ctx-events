import { formatPrice } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import { Stepper } from '@contexis/wp-react-form';
import type { TicketInfo } from '../types';

type Props = {
	ticket: TicketInfo;
	count: number;
	onChange: (count: number) => void;
};

export function TicketCounter({ ticket, count, onChange }: Props) {
	const max = ticket.booking_limit ?? ticket.ticket_limit_per_booking ?? 10;

	return (
		<div className="booking-ticket" data-testid={`booking-ticket-${ticket.id}`}>
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
			<Stepper
				className="booking-ticket__counter"
				value={count}
				min={0}
				max={max}
				onChange={onChange}
				decrementLabel={__('Remove one', 'ctx-events')}
				incrementLabel={__('Add one', 'ctx-events')}
				decrementTestId={`booking-ticket-${ticket.id}-decrement`}
				incrementTestId={`booking-ticket-${ticket.id}-increment`}
			/>
		</div>
	);
}
