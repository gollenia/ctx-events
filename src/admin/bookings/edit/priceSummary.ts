import type {
	BookingAttendeeResource,
	PriceSummary,
} from 'src/types/types';

type CalculatePriceSummaryArgs = {
	attendees: BookingAttendeeResource[];
	currentPrice: PriceSummary;
	donationCents: number;
};

export const calculateBookingPriceSummary = ({
	attendees,
	currentPrice,
	donationCents,
}: CalculatePriceSummaryArgs): PriceSummary => {
	const currency = currentPrice.finalPrice.currency;
	const bookingPriceCents = attendees.reduce((sum, attendee) => {
		return sum + attendee.ticketPrice.amountCents;
	}, 0);
	const discountAmountCents = currentPrice.discountAmount.amountCents ?? 0;
	const finalPriceCents = Math.max(
		0,
		bookingPriceCents - discountAmountCents + donationCents,
	);

	return {
		bookingPrice: {
			...currentPrice.bookingPrice,
			amountCents: bookingPriceCents,
			currency,
		},
		donationAmount: {
			...currentPrice.donationAmount,
			amountCents: donationCents,
			currency,
		},
		discountAmount: {
			...currentPrice.discountAmount,
			amountCents: discountAmountCents,
			currency,
		},
		finalPrice: {
			...currentPrice.finalPrice,
			amountCents: finalPriceCents,
			currency,
		},
	};
};
