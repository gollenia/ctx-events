import type { CSSProperties } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { CalendarDay } from './types';
import {
	DAY_LABELS,
	formatCalendarTime,
	getCreateEventUrl,
	lightenHexColor,
} from './utils';

interface CalendarGridProps {
	days: Array<CalendarDay>;
	loading: boolean;
}

const CalendarGrid = ({ days, loading }: CalendarGridProps) => {
	const getEventStyle = (
		color: string | null | undefined,
	): CSSProperties | undefined => {
		if (!color) {
			return undefined;
		}

		return {
			'--ctx-events-calendar-event-accent': color,
			'--ctx-events-calendar-event-bg': lightenHexColor(color, 0.9),
			'--ctx-events-calendar-event-border': lightenHexColor(color, 0.78),
		} as CSSProperties;
	};

	return (
		<div
			className={`ctx-events-calendar__grid ${loading ? 'is-loading' : ''}`}
			role="grid"
			aria-busy={loading}
		>
			{DAY_LABELS.map((label) => (
				<div
					key={label}
					className="ctx-events-calendar__weekday"
					role="columnheader"
				>
					{label}
				</div>
			))}

			{days.map((day) => (
				<div
					key={day.key}
					className={`ctx-events-calendar__day ${day.inMonth ? '' : 'is-outside-month'}`}
					role="gridcell"
					tabIndex={loading ? -1 : 0}
					onClick={() => {
						if (loading) {
							return;
						}
						window.location.href = getCreateEventUrl(day.date);
					}}
					onKeyDown={(event) => {
						if (loading) {
							return;
						}
						if (event.key === 'Enter' || event.key === ' ') {
							event.preventDefault();
							window.location.href = getCreateEventUrl(day.date);
						}
					}}
				>
					<div className="ctx-events-calendar__day-number">
						{day.date.getDate()}
					</div>
					<div className="ctx-events-calendar__events">
						{day.events.map((event) => (
							<a
								key={event.id}
								className="ctx-events-calendar__event"
								href={`/wp-admin/post.php?post=${event.id}&action=edit`}
								onClick={(clickEvent) => clickEvent.stopPropagation()}
								style={getEventStyle(event.color)}
							>
								<span className="ctx-events-calendar__event-time">
									{formatCalendarTime(event.startDate, false)}
								</span>
								<strong
									className="ctx-events-calendar__event-title"
									title={event.title || __('(No title)', 'ctx-events')}
								>
									{event.title || __('(No title)', 'ctx-events')}
								</strong>
							</a>
						))}
					</div>
				</div>
			))}

			{loading && (
				<div className="ctx-events-calendar__overlay" aria-hidden="true">
					<div className="ctx-events-calendar__spinner" />
					<span>{__('Loading calendar…', 'ctx-events')}</span>
				</div>
			)}
		</div>
	);
};

export default CalendarGrid;
