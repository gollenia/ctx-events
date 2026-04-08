import { Separator } from '@base-ui/react';
import { formatDateRange } from '@events/i18n';
import { __ } from '@wordpress/i18n';
import type { ReactNode } from 'react';
import type { BookingData, BookingState, SectionId } from '../types';
import { BookingCard } from './BookingCard';
import { PriceSummary } from './PriceSummary';
import { SectionHeading } from './SectionHeading';

type Props = {
	data: BookingData;
	state: BookingState;
	visible?: boolean;
	className?: string;
	couponField?: ReactNode;
	showCouponField?: boolean;
};

const sectionLabels: Record<Exclude<SectionId, 'success'>, string> = {
	tickets: __('Tickets', 'ctx-events'),
	attendees: __('Attendees', 'ctx-events'),
	booking: __('Your details', 'ctx-events'),
	payment: __('Payment', 'ctx-events'),
};

export function BookingSummaryPanel({
	data,
	state,
	className = '',
	couponField = null,
	showCouponField = false,
	visible = true,
}: Props) {
	const summaryClassName = ['booking-summary-panel', className]
		.filter(Boolean)
		.join(' ');
	const attendeeCount = state.attendees.length;
	const currentStep =
		state.openSection === 'success'
			? __('Done', 'ctx-events')
			: sectionLabels[state.openSection];

	return visible ? (
		<aside className={summaryClassName} data-testid="booking-summary-panel">
			<BookingCard className="booking-summary-panel__card" variant="summary">
				<SectionHeading title={__('Booking overview', 'ctx-events')} />
				<h3 className="booking-summary-panel__title">{data.eventName}</h3>
				<p className="booking-summary-panel__date">
					{formatDateRange(data.eventStartDate, data.eventEndDate)}
				</p>

				<Separator />

				{attendeeCount > 0 ? (
					<>
						<PriceSummary
							tickets={data.tickets}
							attendees={state.attendees}
							coupon={state.couponCheckResult}
							donationAmount={state.donationAmount}
						/>
						{showCouponField && couponField}
					</>
				) : (
					<p className="booking-summary-panel__empty">
						{__(
							'Select at least one ticket to start your booking.',
							'ctx-events',
						)}
					</p>
				)}
			</BookingCard>
		</aside>
	) : null;
}
