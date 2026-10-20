import { formatDate, formatDateRange } from '@events/i18n';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
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
		tableShowHeader,
		tableShowDividers,
		tableAlternatingRows,
		tableHoverEffect,
		tableShowCalendarIcon,
		tableColumns,
	},
	events,
}: UpcomingViewProps) {
	const configuredColumns = tableColumns ?? [
		'title',
		...(showCategory ? ['category'] : []),
		'description',
		...(showAudience ? ['audience'] : []),
		...(showPerson ? ['person'] : []),
		...(showLocation ? ['location'] : []),
		...(showBookedUp ? ['booking'] : []),
	];
	const columns = tableShowCalendarIcon && !configuredColumns.includes('date')
		? ['date', ...configuredColumns]
		: configuredColumns;
	const className = [
		'ctx-upcoming-table',
		tableShowDividers ? '' : 'ctx-upcoming-table--no-dividers',
		tableAlternatingRows ? 'ctx-upcoming-table--alternating' : '',
		tableHoverEffect ? 'ctx-upcoming-table--hover' : '',
	]
		.filter(Boolean)
		.join(' ');
	const columnLabels: Record<string, string> = {
		date: __('Date', 'ctx-events'),
		title: __('Event', 'ctx-events'),
		category: __('Category', 'ctx-events'),
		description: __('Description', 'ctx-events'),
		audience: __('Audience', 'ctx-events'),
		person: __('Person', 'ctx-events'),
		location: __('Location', 'ctx-events'),
		booking: __('Booking', 'ctx-events'),
	};
	const renderCell = (item: UpcomingViewEvent, column: string) => {
		const location = getLocationLabel(item, showLocation);
		switch (column) {
			case 'date':
				return (
					<td className="ctx-upcoming-table__date">
						{tableShowCalendarIcon && (
							<EventIcon
								name="date"
								className="ctx-upcoming-table__calendar-icon"
							/>
						)}
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
				);
			case 'title':
				return (
					<td className="ctx-upcoming-table__title">
						<a href={item.link}>
							<b className="ctx-upcoming-table__title-text">{item.title}</b>
						</a>
						<div className="ctx-upcoming-table__subtitle">
							{formatDateRange(item.start, item.end)}
						</div>
					</td>
				);
			case 'category':
				return (
					<td className="ctx-upcoming-table__label">{item.category?.name}</td>
				);
			case 'description':
				return (
					<td className="ctx-upcoming-table__text">
						{truncate(item.excerpt, excerptLength)}
					</td>
				);
			case 'audience':
				return (
					<td className="ctx-upcoming-table__text ctx-upcoming-table__audience">
						{item.audience}
					</td>
				);
			case 'person':
				return (
					<td className="ctx-upcoming-table__text ctx-upcoming-table__speaker">
						{item.person?.name}
					</td>
				);
			case 'location':
				return (
					<td className="ctx-upcoming-table__text ctx-upcoming-table__location">
						{location}
					</td>
				);
			case 'booking':
				return (
					<td>
						{renderBookingWarning(item, showBookedUp, bookedUpWarningThreshold)}
					</td>
				);
			default:
				return null;
		}
	};

	return (
		<table className={className} cellPadding={0} cellSpacing={0}>
			{tableShowHeader && (
				<thead>
					<tr>
						{columns.map((column) => (
							<th key={column} scope="col">
								{columnLabels[column]}
							</th>
						))}
					</tr>
				</thead>
			)}
			<tbody>
				{events.map((item) => {
					return (
						<tr
							className="ctx-upcoming-table__row"
							key={item.id}
							onClick={() => {
								window.location.href = item.link;
							}}
						>
							{columns.map((column) => (
								<Fragment key={column}>{renderCell(item, column)}</Fragment>
							))}
						</tr>
					);
				})}
			</tbody>
		</table>
	);
}

export default TableView;
