import type { DataFilterField } from '@events/datatable/Filter';
import { useMemo } from '@wordpress/element';
import type { TimeScope } from '../../../types/types';
import CalendarGrid from './CalendarGrid';
import CalendarToolbar from './CalendarToolbar';
import { useFetchEventCalendar } from './useFetchEventCalendar';
import { useStoredCalendarMonth } from './useStoredCalendarMonth';
import { buildCalendarDays, getMonthFromScope } from './utils';

interface EventCalendarViewProps {
	filters: Array<DataFilterField>;
	scope: TimeScope;
}

const EventCalendarView = ({ filters, scope }: EventCalendarViewProps) => {
	const initialMonth = useMemo(() => getMonthFromScope(scope, []), [scope]);
	const { activeMonth, setActiveMonth } = useStoredCalendarMonth(
		'ctx-events:admin:events:calendar-month',
		initialMonth,
	);
	const { events, loading } = useFetchEventCalendar(activeMonth, filters);

	const days = useMemo(
		() => buildCalendarDays(activeMonth, events),
		[activeMonth, events],
	);

	const monthLabel = useMemo(
		() =>
			new Intl.DateTimeFormat(undefined, {
				year: 'numeric',
				month: 'long',
			}).format(activeMonth),
		[activeMonth],
	);

	return (
		<div className="ctx-events-calendar">
			<CalendarToolbar
				activeMonth={activeMonth}
				loading={loading}
				monthLabel={monthLabel}
				onPreviousMonth={() =>
					setActiveMonth(
						new Date(
							activeMonth.getFullYear(),
							activeMonth.getMonth() - 1,
							1,
						),
					)
				}
				onCurrentMonth={() => {
					const now = new Date();
					setActiveMonth(new Date(now.getFullYear(), now.getMonth(), 1));
				}}
				onNextMonth={() =>
					setActiveMonth(
						new Date(
							activeMonth.getFullYear(),
							activeMonth.getMonth() + 1,
							1,
						),
					)
				}
			/>
			<CalendarGrid days={days} loading={loading} />
		</div>
	);
};

export default EventCalendarView;
