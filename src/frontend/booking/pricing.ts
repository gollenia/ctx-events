import type { AttendeePayload, CouponCheckResult, TicketInfo } from './types';

export function calculateBookingTotal(
	tickets: TicketInfo[],
	attendees: AttendeePayload[],
): number {
	const ticketCounts = attendees.reduce<Record<string, number>>((counts, attendee) => {
		counts[attendee.ticket_id] = (counts[attendee.ticket_id] ?? 0) + 1;
		return counts;
	}, {});

	return tickets.reduce(
		(sum, ticket) =>
			sum +
			Number(ticket.price.amountCents || 0) *
				Number(ticketCounts[ticket.id] ?? 0),
		0,
	);
}

export function calculateCouponDiscount(
	total: number,
	coupon: CouponCheckResult | null | undefined,
): number {
	if (!coupon || total <= 0) {
		return 0;
	}

	if (coupon.discountType === 'percent') {
		return Math.min(
			total,
			Math.max(0, Math.round(total * (coupon.discountValue / 100))),
		);
	}

	return Math.min(total, Math.max(0, coupon.discountValue));
}
