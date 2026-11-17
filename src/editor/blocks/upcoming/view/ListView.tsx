import { Icon, commentAuthorAvatar } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

import { List, ListItem } from '@contexis/wp-ui/list';

import { formatDailySchedule, formatDate, formatDateRange } from '@events/i18n';

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

function DateLeading({ event }: { event: UpcomingViewEvent }) {
	return (
		<time className="ctx-upcoming-list__date" dateTime={event.start}>
			<span>{formatDate(event.start, { month: 'short' })}</span>
			<strong>{formatDate(event.start, { day: 'numeric' })}</strong>
		</time>
	);
}

function PersonLeading({ event }: { event: UpcomingViewEvent }) {
	const image = event.person?.image;

	if (image?.url) {
		return (
			<div className="ctx-upcoming-list__portrait" aria-hidden="true">
				<img src={image.sizes?.large?.url || image.url} alt="" />
			</div>
		);
	}

	return (
		<div className="ctx-upcoming-list__person-placeholder" aria-hidden="true">
			<Icon icon={commentAuthorAvatar} />
		</div>
	);
}

function EventImageLeading({ event }: { event: UpcomingViewEvent }) {
	if (event.image?.url) {
		return (
			<div className="ctx-upcoming-list__image" aria-hidden="true">
				<img
					src={event.image.sizes?.large?.url || event.image.url}
					alt=""
				/>
			</div>
		);
	}

	return <DateLeading event={event} />;
}

function getLeading(event: UpcomingViewEvent, leading: 'event' | 'person' | 'date') {
	switch (leading) {
		case 'person':
			return <PersonLeading event={event} />;
		case 'date':
			return <DateLeading event={event} />;
		case 'event':
			return <EventImageLeading event={event} />;
	}
}

function ListView({
	attributes: {
		showImages,
		listLeading,
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
	const resolvedListLeading = listLeading || (showImages ? 'event' : 'date');

	return (
		<List className="ctx-upcoming-list">
			{events.map((item) => {
				const location = getLocationLabel(item, showLocation);

				return (
					<ListItem
						className="ctx-upcoming-list__item"
						key={item.id}
						leading={getLeading(item, resolvedListLeading)}
					>
						<div className="ctx-upcoming-list__content">
							{item.category && showCategory ? (
								<span className="ctx-upcoming-list__label">{item.category.name}</span>
							) : null}
							<a href={item.link} className="ctx-upcoming-list__event-link">
								<h3 className="ctx-upcoming-list__title">{item.title}</h3>
							</a>
							<p className="ctx-upcoming-list__schedule">
								{item.isDaily
									? formatDailySchedule(item.start, item.end)
									: formatDateRange(item.start, item.end)}
							</p>

							<p className="ctx-upcoming-list__text">
								{truncate(item.excerpt, excerptLength)}
							</p>
							{showAudience || showPerson || showLocation || showBookedUp ? (
								<div className="ctx-upcoming-list__meta">
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
					</ListItem>
				);
			})}
		</List>
	);
}

export default ListView;
