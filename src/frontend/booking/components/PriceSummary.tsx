import { formatPrice } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import { calculateBookingTotal, calculateCouponDiscount } from '../pricing';
import type { CouponCheckResult, TicketInfo } from '../types';

type Props = {
	tickets: TicketInfo[];
	ticketCounts: Record<string, number>;
	coupon?: CouponCheckResult | null;
};

export function PriceSummary({ tickets, ticketCounts, coupon }: Props) {
	const lines = tickets.filter((t) => (ticketCounts[t.id] ?? 0) > 0);

	if (lines.length === 0) return null;

	const currency = lines[0]?.price.currency ?? 'EUR';
	const total = calculateBookingTotal(lines, ticketCounts);
	const discount = calculateCouponDiscount(total, coupon);
	const totalAfterDiscount = Math.max(0, total - discount);

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
							{ticket.price.amountCents === 0
								? __('Free', 'ctx-events')
								: formatPrice({
										amountCents: ticket.price.amountCents * count,
										currency: ticket.price.currency,
									})}
						</span>
					</div>
				);
			})}
			<div className="booking-price-summary__total">
				<span className="booking-price-summary__label">
					{__('Subtotal', 'ctx-events')}
				</span>
				<span className="booking-price-summary__amount">
					{total === 0
						? __('Free', 'ctx-events')
						: formatPrice({ amountCents: total, currency })}
				</span>
			</div>
			{discount > 0 && coupon && (
				<div className="booking-price-summary__line booking-price-summary__line--discount">
					<span className="booking-price-summary__label">
						{__('Coupon discount', 'ctx-events')}
					</span>
					<span className="booking-price-summary__amount">
						-{formatPrice({ amountCents: discount, currency })}
					</span>
				</div>
			)}
			<div className="booking-price-summary__total">
				<span className="booking-price-summary__label">
					{__('Total due', 'ctx-events')}
				</span>
				<span className="booking-price-summary__amount">
					{totalAfterDiscount === 0
						? __('Free', 'ctx-events')
						: formatPrice({ amountCents: totalAfterDiscount, currency })}
				</span>
			</div>
		</div>
	);
}
