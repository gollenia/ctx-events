import { formatPrice } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import { calculateBookingTotal, calculateCouponDiscount } from '../pricing';
import type { AttendeePayload, CouponCheckResult, TicketInfo } from '../types';

type Props = {
	tickets: TicketInfo[];
	attendees: AttendeePayload[];
	coupon?: CouponCheckResult | null;
	donationAmount?: number;
};

export function PriceSummary({
	tickets,
	attendees,
	coupon,
	donationAmount = 0,
}: Props) {
	const ticketCounts = attendees.reduce<Record<string, number>>((counts, attendee) => {
		counts[attendee.ticket_id] = (counts[attendee.ticket_id] ?? 0) + 1;
		return counts;
	}, {});
	const lines = tickets.filter((t) => (ticketCounts[t.id] ?? 0) > 0);

	if (lines.length === 0) return null;

	const currency = lines[0]?.price.currency ?? 'EUR';
	const total = calculateBookingTotal(lines, attendees);
	const discount = calculateCouponDiscount(total, coupon);
	const normalizedDonationAmount = Math.max(0, donationAmount);
	const totalAfterDiscount = Math.max(0, total - discount);
	const finalTotal = totalAfterDiscount + normalizedDonationAmount;

	return (
		<div className="booking-price-summary" data-testid="booking-price-summary">
			{lines.map((ticket) => {
				const count = ticketCounts[ticket.id] ?? 0;
				return (
					<div key={ticket.id} className="booking-price-summary__line">
						<span className="booking-price-summary__label">
							<span className="booking-price-summary__count">{count}</span>
							<span className="booking-price-summary__separator">x</span>
							<span>{ticket.name}</span>
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
			{normalizedDonationAmount > 0 && (
				<div className="booking-price-summary__line booking-price-summary__line--donation">
					<span className="booking-price-summary__label">
						{__('Contribution', 'ctx-events')}
					</span>
					<span className="booking-price-summary__amount">
						{formatPrice({ amountCents: normalizedDonationAmount, currency })}
					</span>
				</div>
			)}
			<div className="booking-price-summary__total">
				<span className="booking-price-summary__label">
					{__('Total due', 'ctx-events')}
				</span>
				<span className="booking-price-summary__amount">
					{finalTotal === 0
						? __('Free', 'ctx-events')
						: formatPrice({ amountCents: finalTotal, currency })}
				</span>
			</div>
		</div>
	);
}
