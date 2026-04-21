import { __ } from '@wordpress/i18n';

import { formatDateRange } from '@events/i18n';

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

function ListView({
	attributes: {
		showImages,
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
		<div className="event-list">
			{events.map((item) => {
				const location = getLocationLabel(item, showLocation);

				return (
					<div className="event-card" key={item.id}>
						{showImages && item.image?.url ? (
							<a href={item.link} className="event-card-image">
								<img
									src={item.image.sizes?.large?.url || item.image.url}
									alt={item.image.altText || item.title}
								/>
							</a>
						) : null}
						<div className="event-card-content">
							{item.category && showCategory ? (
								<span className="event-card-label">{item.category.name}</span>
							) : null}
							<h5 className="event-card-subtitle">
								{formatDateRange(item.start, item.end)}
							</h5>
							<a href={item.link}>
								<h4 className="event-card-title">{item.title}</h4>
							</a>

							<p className="event-card-text">
								{truncate(item.excerpt, excerptLength)}
							</p>
							{showAudience || showPerson || showLocation || showBookedUp ? (
								<div className="card__footer card__subtitle pills pills--small">
									{showAudience && item.audience?.length ? (
										<span className="pills__item event__audience">
											{item.audience}
										</span>
									) : null}
									{showPerson === 'name' && item.person?.id ? (
										<span className="pills__item event__speaker">
											{item.person.name}
										</span>
									) : null}
									{showLocation && item.location?.id ? (
										<span className="pills__item event__location">
											{location}
										</span>
									) : null}
									{renderBookingWarning(
										item,
										showBookedUp,
										bookedUpWarningThreshold,
									)}
								</div>
							) : null}
						</div>
					</div>
				);
			})}
		</div>
	);
}

export default ListView;
