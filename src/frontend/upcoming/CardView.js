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
			showAudience,
			showPerson,
			animateOnScroll,
			animationType,
		},
		events,
	} = props;

	const className = [
		'event-grid',
		animateOnScroll ? 'ctx-animate-children' : '',
		animationType ? `ctx-${animationType}` : '',
	].join(' ');

	return (
		<ul className={className}>
			{events.map((item, index) => {
				console.log(item);
				const location =
					item.location && ['city', 'name'].includes(showLocation)
						? item.location[showLocation]
						: '';

				const bookingWarning = () => {
					if (!showBookedUp || !item.bookings?.hasBookings) return null;
					if (item.bookings?.spaces > bookedUpWarningThreshold) return null;

					if (item.bookings?.spaces > 0) {
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

				return (
					<li className="event-card" key={index}>
						{showPerson === 'image' && item.person && (
							<div className="event-card-speaker">
								<div>
									<div>{item.person.name}</div>
								</div>
							</div>
						)}
						{showImages && item.image?.url && (
							<a href={item.link} className="event-card-image">
								<img
									src={item.image.sizes?.large?.url || item.image.url}
									alt={item.image.altText || item.title}
								/>
							</a>
						)}
						<div className="event-card-content">
							{item.category && showCategory && (
								<span className="event-card-label">{item.category.name}</span>
							)}
							<a href={item.link}>
								<h2 className="event-card-title">{item.title}</h2>
							</a>
							<h4 className="event-card-subtitle">
								{formatDateRange(item.start, item.end)}
							</h4>
							<p className="event-card-text">
								{truncate(item.excerpt, excerptLength)}
							</p>
							{(showAudience ||
								showPerson ||
								showLocation ||
								showBookedUp) && (
								<div className="event-card-footer">
									<div className="event-card-footer-details">
										<div className="event-card-footer-details-text">
											{showLocation && item.location?.id && (
												<div className="event-card-detail">
													<i className="material-icons">place</i>{' '}
													<span>{location}</span>
												</div>
											)}
											{showAudience && item.audience?.length > 0 && (
												<div className="event-card-detail">
													<i className="material-icons">groups</i>{' '}
													<span>{item.audience}</span>
												</div>
											)}
										</div>
									</div>

									{showBookedUp && item.bookings && bookingWarning()}
								</div>
							)}
						</div>
					</li>
				);
			})}
		</ul>
	);
}

export default EventCards;
