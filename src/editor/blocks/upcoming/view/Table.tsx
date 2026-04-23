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
			<span className="ctx-upcoming-pill">
				{__('Nearly Booked up', 'ctx-events')}
			</span>
		);
	}

	return (
		<span className="ctx-upcoming-pill ctx-upcoming-pill--error">
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
		<table className="ctx-upcoming-table" cellPadding={0} cellSpacing={0}>
			<tbody>
				{events.map((item) => {
					const location = getLocationLabel(item, showLocation);

					return (
							<tr
								className="ctx-upcoming-table__row"
							key={item.id}
							onClick={() => {
								window.location.href = item.link;
							}}
						>
								<td className="ctx-upcoming-table__date">
									<div className="ctx-upcoming-date">
										<span className="ctx-upcoming-date__day--numeric">
										{formatDate(item.start, { day: 'numeric' })}
									</span>
										<span className="ctx-upcoming-date__day--short">
										{formatDate(item.start, { weekday: 'short' })}
									</span>
										<span className="ctx-upcoming-date__day--long">
										{formatDate(item.start, { weekday: 'long' })}
									</span>
										<span className="ctx-upcoming-date__month--long">
										{formatDate(item.start, { month: 'long' })}
									</span>
										<span className="ctx-upcoming-date__month--numeric">
										{formatDate(item.start, { month: 'numeric' })}
									</span>
										<span className="ctx-upcoming-date__month--short">
										{formatDate(item.start, { month: 'short' })}
									</span>
								</div>
							</td>

								<td className="ctx-upcoming-table__title">
									<a href={item.link}>
										<b className="ctx-upcoming-table__title-text">{item.title}</b>
									</a>
									<div className="ctx-upcoming-table__subtitle">
									{formatDateRange(item.start, item.end)}
								</div>
							</td>
								{showCategory ? (
									<td className="ctx-upcoming-table__label">{item.category?.name}</td>
								) : null}

								<td className="ctx-upcoming-table__text">
								{truncate(item.excerpt, excerptLength)}
							</td>

								{showAudience ? (
									<td className="ctx-upcoming-table__text ctx-upcoming-table__audience">
									{item.audience}
								</td>
							) : null}
								{showPerson ? (
									<td className="ctx-upcoming-table__text ctx-upcoming-table__speaker">
									{item.person?.name}
								</td>
							) : null}
								{showLocation ? (
									<td className="ctx-upcoming-table__text ctx-upcoming-table__location">
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
