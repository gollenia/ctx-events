import { Flex, FlexItem, Stepper } from '@contexis/wp-react-form';
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

	return (
		<Flex
			className="booking-ticket"
			align="center"
			justify="space-between"
			data-testid={`booking-ticket-${ticket.id}`}
		>
			<FlexItem className="booking-ticket__info" flex="1">
				<span className="booking-ticket__name">{ticket.name}</span>
				<span className="booking-ticket__price">
					{ticket.price.amountCents === 0
						? __('Free', 'ctx-events')
						: formatPrice({
								amountCents: ticket.price.amountCents,
								currency: ticket.price.currency,
							})}
				</span>
			</FlexItem>
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
		</Flex>
	);
}
