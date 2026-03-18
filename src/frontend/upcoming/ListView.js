/**
 * External Dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal Dependencies
 */
import { formatDateRange } from '@events/i18n';
import truncate from './truncate';

function EventCards(props) {
	const {
		attributes: {
			showImages,
			showCategory,
			showLocation,
			showBookedUp,
			bookedUpWarningThreshold,
			excerptLength,
			textAlignment,
			showAudience,
			showPerson,
		},
		events,
	} = props;

	return (
		<div className="event-list">
			{events.map((item, index) => {
				const location =
					item.location && ['city', 'name'].includes(showLocation)
						? item.location[showLocation]
						: '';

				const bookingWarning = () => {
					if (!showBookedUp || !item.bookings?.hasBookings) return null;
					if (item.bookings?.spaces > bookedUpWarningThreshold) return null;

					if (item.bookings?.spaces > 0) {
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

				return (
					<div className="event-card" key={index}>
						{showImages && item.image?.url && (
							<a href={item.link} className="event-card-image">
								<img
									src={item.image?.sizes?.large?.url || item.image?.url}
									alt={item.image?.altText || item.title}
								/>
							</a>
						)}
						<div className="event-card-content">
							{item.category && showCategory && (
								<span className="event-card-label">{item.category.name}</span>
							)}
							<h5 className="event-card-subtitle">
								{formatDateRange(item.start, item.end)}
							</h5>
							<a href={item.link}>
								<h4 className="event-card-title">{item.title}</h4>
							</a>

							<p className="event-card-text">
								{truncate(item.excerpt, excerptLength)}
							</p>
							{(showAudience ||
								showPerson ||
								showLocation ||
								showBookedUp) && (
								<div className="card__footer card__subtitle pills pills--small">
									{showAudience && item.audience?.length > 0 && (
										<span className="pills__item event__audience">
											{item.audience}
										</span>
									)}
									{showPerson === 'name' && item.person?.id && (
										<span className="pills__item event__speaker">
											{item.person.name}
										</span>
									)}
									{showLocation && item.location?.id && (
										<span className="pills__item event__location">
											{location}
										</span>
									)}
									{showBookedUp && item.bookings && bookingWarning()}
								</div>
							)}
						</div>
					</div>
				);
			})}
		</div>
	);
}

export default EventCards;
