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
		<div className="ctx-upcoming-list">
			{events.map((item) => {
				const location = getLocationLabel(item, showLocation);

				return (
					<div className="ctx-upcoming-card" key={item.id}>
						{showImages && item.image?.url ? (
							<a href={item.link} className="ctx-upcoming-card__image">
								<img
									src={item.image.sizes?.large?.url || item.image.url}
									alt={item.image.altText || item.title}
								/>
							</a>
						) : null}
						<div className="ctx-upcoming-card__content">
							{item.category && showCategory ? (
								<span className="ctx-upcoming-card__label">{item.category.name}</span>
							) : null}
							<h5 className="ctx-upcoming-card__subtitle">
								{formatDateRange(item.start, item.end)}
							</h5>
							<a href={item.link}>
								<h4 className="ctx-upcoming-card__title">{item.title}</h4>
							</a>

							<p className="ctx-upcoming-card__text">
								{truncate(item.excerpt, excerptLength)}
							</p>
							{showAudience || showPerson || showLocation || showBookedUp ? (
								<div className="ctx-upcoming-card__meta">
									{showAudience && item.audience?.length ? (
										<span className="ctx-upcoming-pill ctx-upcoming-pill--audience">
											{item.audience}
										</span>
									) : null}
									{showPerson === 'name' && item.person?.id ? (
										<span className="ctx-upcoming-pill ctx-upcoming-pill--speaker">
											{item.person.name}
										</span>
									) : null}
									{showLocation && item.location?.id ? (
										<span className="ctx-upcoming-pill ctx-upcoming-pill--location">
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
