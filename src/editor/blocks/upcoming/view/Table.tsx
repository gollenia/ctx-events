import { __ } from '@wordpress/i18n';

import { formatDate, formatDateRange } from '@events/i18n';

import truncate from './truncate';
import type { UpcomingViewEvent, UpcomingViewProps } from './types';

const getLocationLabel = (
	event: UpcomingViewEvent,
	showLocation: '' | 'name' | 'city' | 'country' | 'state',
) => {
	if (!event.location || !['city', 'name'].includes(showLocation)) {
		return '';
	}

	return event.location[showLocation];
};

const renderBookingWarning = (
	event: UpcomingViewEvent,
	showBookedUp: boolean,
	bookedUpWarningThreshold: number,
) => {
	if (!showBookedUp || !event.bookings?.hasBookings) {
		return null;
	}

	if (
		event.bookings.spaces === null &&
		event.bookings.denyReason !== 'sold_out'
	) {
		return null;
	}

	if (
		event.bookings.spaces !== null &&
		event.bookings.spaces > bookedUpWarningThreshold
	) {
		return null;
	}

	if (event.bookings.spaces !== null && event.bookings.spaces > 0) {
		return (
			<span className="pills__item pills__item--warning">
				{__('Nearly Booked up', 'ctx-events')}
			</span>
		);
	}

	return (
		<span className="pills__item pills__item--error">
			{__('Booked up', 'ctx-events')}
		</span>
	);
};

function TableView({
	attributes: {
		showCategory,
		showLocation,
		showBookedUp,
		bookedUpWarningThreshold,
		excerptLength,
		showAudience,
		showPerson,
	},
	events,
}: UpcomingViewProps) {
	return (
		<table className="event-table" cellPadding={0} cellSpacing={0}>
			<tbody>
				{events.map((item) => {
					const location = getLocationLabel(item, showLocation);

					return (
						<tr
							className="event-row"
							key={item.id}
							onClick={() => {
								window.location.href = item.link;
							}}
						>
							<td className="event-table-date">
								<div className="description__date">
									<span className="date__day--numeric">
										{formatDate(item.start, { day: 'numeric' })}
									</span>
									<span className="date__day--short">
										{formatDate(item.start, { weekday: 'short' })}
									</span>
									<span className="date__day--long">
										{formatDate(item.start, { weekday: 'long' })}
									</span>
									<span className="date__month--long">
										{formatDate(item.start, { month: 'long' })}
									</span>
									<span className="date__month--numeric">
										{formatDate(item.start, { month: 'numeric' })}
									</span>
									<span className="date__month--short">
										{formatDate(item.start, { month: 'short' })}
									</span>
								</div>
							</td>

							<td className="event-table-title">
								<a href={item.link}>
									<b className="event-table-title">{item.title}</b>
								</a>
								<div className="event-table-subtitle">
									{formatDateRange(item.start, item.end)}
								</div>
							</td>
							{showCategory ? (
								<td className="event-table-label">{item.category?.name}</td>
							) : null}

							<td className="event-table-text">
								{truncate(item.excerpt, excerptLength)}
							</td>

							{showAudience ? (
								<td className="event-table-text event-table-audience">
									{item.audience}
								</td>
							) : null}
							{showPerson ? (
								<td className="event-table-text event-table-speaker">
									{item.person?.name}
								</td>
							) : null}
							{showLocation ? (
								<td className="event-table-text event-table-location">
									{location}
								</td>
							) : null}
							{showBookedUp ? (
								<td>
									{renderBookingWarning(
										item,
										showBookedUp,
										bookedUpWarningThreshold,
									)}
								</td>
							) : null}
						</tr>
					);
				})}
			</tbody>
		</table>
	);
}

export default TableView;
