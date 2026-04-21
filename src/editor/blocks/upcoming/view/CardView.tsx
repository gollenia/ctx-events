import { __ } from '@wordpress/i18n';

import { formatDateRange } from '@events/i18n';
import EventIcon from '../../../../shared/icons/EventIcon';

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
			<span className="event-card-pill event-card-pill-warning">
				{__('Nearly Booked up', 'ctx-events')}
			</span>
		);
	}

	return (
		<span className="event-card-pill event-card-pill-error">
			{__('Booked up', 'ctx-events')}
		</span>
	);
};

function EventCards({
	attributes: {
		showImages,
		showCategory,
		showLocation,
		showBookedUp,
		bookedUpWarningThreshold,
		excerptLength,
		showAudience,
		showPerson,
		animateOnScroll,
		animationType,
	},
	events,
}: UpcomingViewProps) {
	const className = [
		'event-grid',
		animateOnScroll ? 'ctx-animate-children' : '',
		animationType ? `ctx-${animationType}` : '',
	]
		.filter(Boolean)
		.join(' ');

	return (
		<ul className={className}>
			{events.map((item) => {
				const location = getLocationLabel(item, showLocation);

				return (
					<li className="event-card" key={item.id}>
						{showPerson === 'image' && item.person ? (
							<div className="event-card-speaker">
								<div>
									<div>{item.person.name}</div>
								</div>
							</div>
						) : null}
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
							<a href={item.link}>
								<h2 className="event-card-title">{item.title}</h2>
							</a>
							<h4 className="event-card-subtitle">
								{formatDateRange(item.start, item.end)}
							</h4>
							<p className="event-card-text">
								{truncate(item.excerpt, excerptLength)}
							</p>
							{showAudience || showPerson || showLocation || showBookedUp ? (
								<div className="event-card-footer">
									<div className="event-card-footer-details">
										<div className="event-card-footer-details-text">
											{showLocation && item.location?.id ? (
												<div className="event-card-detail">
													<EventIcon name="location" />{' '}
													<span>{location}</span>
												</div>
											) : null}
											{showAudience && item.audience?.length ? (
												<div className="event-card-detail">
													<EventIcon name="audience" />{' '}
													<span>{item.audience}</span>
												</div>
											) : null}
										</div>
									</div>

									{renderBookingWarning(
										item,
										showBookedUp,
										bookedUpWarningThreshold,
									)}
								</div>
							) : null}
						</div>
					</li>
				);
			})}
		</ul>
	);
}

export default EventCards;
