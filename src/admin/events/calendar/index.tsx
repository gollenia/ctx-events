import 'temporal-polyfill/global';
import '@schedule-x/theme-default/dist/index.css';
import {
	createCalendar,
	createViewWeek,
	createViewYearGrid,
	createViewMonthGrid,
	type CalendarEvent as ScheduleEvent,
	type CalendarType,
	type PluginBase,
} from '@schedule-x/calendar';
import { createEventModalPlugin } from '@schedule-x/event-modal';
import apiFetch from '@wordpress/api-fetch';
import {
	createRoot,
	useEffect,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { DataFilterField } from '@events/datatable/Filter';
import type { Event, TimeScope } from '../../../types/types';
import { getLocale } from '@events/i18n';
import { actions } from '../actions';
import EventCancelConfirmModal from '../EventCancelConfirmModal';
import { getCreateEventUrl, getMonthFromScope } from './utils';
import { useStoredCalendarMonth } from './useStoredCalendarMonth';
import {
	type CalendarView,
	useStoredCalendarView,
} from './useStoredCalendarView';
import type { CalendarEvent } from './types';

interface EventCalendarViewProps {
	filters: Array<DataFilterField>;
	scope: TimeScope;
}

const getFilterValue = (
	filters: Array<DataFilterField>,
	field: string,
): DataFilterField['value'] | null =>
	filters.find((filter) => filter.field === field)?.value ?? null;

const getTimezone = (): string =>
	Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';

const isHexColor = (value: string | null): value is string =>
	Boolean(value && /^#[\da-f]{6}$/i.test(value));

const colorNameFor = (color: string): string =>
	`ctx-event-${color.slice(1).toLowerCase()}`;

const lightenColor = (color: string, amount: number): string => {
	const channel = (offset: number): string => {
		const value = Number.parseInt(color.slice(offset, offset + 2), 16);
		return Math.round(value + (255 - value) * amount)
			.toString(16)
			.padStart(2, '0');
	};

	return `#${channel(1)}${channel(3)}${channel(5)}`;
};

const textColorFor = (color: string): string => {
	const red = Number.parseInt(color.slice(1, 3), 16);
	const green = Number.parseInt(color.slice(3, 5), 16);
	const blue = Number.parseInt(color.slice(5, 7), 16);
	const luminance = (red * 299 + green * 587 + blue * 114) / 1000;

	return luminance > 160 ? '#1d2327' : '#ffffff';
};

const registerCategoryColors = (
	events: Array<CalendarEvent>,
	calendars: Record<string, CalendarType>,
): void => {
	for (const event of events) {
		if (!isHexColor(event.color)) continue;

		const colorName = colorNameFor(event.color);
		if (calendars[colorName]) continue;

		const container = lightenColor(event.color, 0.88);
		calendars[colorName] = {
			colorName,
			lightColors: {
				main: event.color,
				container,
				onContainer: textColorFor(container),
			},
		};
		document.documentElement.style.setProperty(
			`--sx-color-${colorName}`,
			event.color,
		);
		document.documentElement.style.setProperty(
			`--sx-color-${colorName}-container`,
			container,
		);
		document.documentElement.style.setProperty(
			`--sx-color-on-${colorName}-container`,
			textColorFor(container),
		);
	}
};

const toScheduleEvent = (
	event: CalendarEvent,
	timezone: string,
): ScheduleEvent => ({
	id: event.id,
	title: event.title,
	description: event.description,
	location: event.locationName ?? undefined,
	people: event.personName ? [event.personName] : undefined,
	recurrenceSeriesId: event.recurrenceSeriesId,
	start: Temporal.Instant.from(event.startDate).toZonedDateTimeISO(timezone),
	end: Temporal.Instant.from(event.endDate).toZonedDateTimeISO(timezone),
	calendarId: isHexColor(event.color) ? colorNameFor(event.color) : undefined,
});

type VirtualOccurrence = {
	id: string;
	type: 'virtual';
	name: string;
	description: string | null;
	startDate: string;
	endDate: string;
	seriesId: number;
};

const toVirtualCalendarEvent = (occurrence: VirtualOccurrence): CalendarEvent => ({
	id: occurrence.id,
	title: `↻ ${occurrence.name}`,
	description: occurrence.description ?? '',
	startDate: occurrence.startDate,
	endDate: occurrence.endDate,
	categoryIds: [],
	color: null,
	locationName: null,
	personName: null,
	recurrenceSeriesId: occurrence.seriesId,
});

const filtersKey = (filters: Array<DataFilterField>): string =>
	JSON.stringify(filters);

const createViewStoragePlugin = (
	onViewChange: (view: CalendarView) => void,
): PluginBase<'ctx-events-view-storage'> => {
	let calendarApp: any;

	return {
		name: 'ctx-events-view-storage',
		beforeRender: ($app) => {
			calendarApp = $app;
		},
		onRangeUpdate: () => {
			const view = calendarApp?.calendarState.view.value;
			if (view === 'month-grid' || view === 'week' || view === 'year-grid') {
				onViewChange(view);
			}
		},
	};
};

interface EventModalActionsProps {
	event: ScheduleEvent;
	onCancelled: (eventId: string | number) => void;
}

const EventModalActions = ({ event, onCancelled }: EventModalActionsProps) => {
	const [isCancelOpen, setIsCancelOpen] = useState(false);
	const cancelAction = actions.find((action) => action.id === 'cancel');
	const eventId = Number(event.id);
	const recurrenceSeriesId = (event as ScheduleEvent & { recurrenceSeriesId?: number }).recurrenceSeriesId;
	const start = event.start as Temporal.ZonedDateTime;
	const end = event.end as Temporal.ZonedDateTime;
	const calendarEvent = {
		id: eventId,
		name: event.title ?? '',
	} as Event;

	if (!cancelAction || (!recurrenceSeriesId && !Number.isInteger(eventId))) return null;

	return (
		<>
			<div className="ctx-events-calendar__modal-details">
				<strong className="ctx-events-calendar__modal-title">
					{event.title || __('(No title)', 'ctx-events')}
				</strong>
				<span>
					{start.toLocaleString(getLocale(), {
						dateStyle: 'medium',
						timeStyle: 'short',
					})}
					{' – '}
					{end.toLocaleString(getLocale(), {
						dateStyle: 'medium',
						timeStyle: 'short',
					})}
				</span>
				{event.people && event.people.length > 0 && (
					<span>{event.people.join(', ')}</span>
				)}
				{event.location && <span>{event.location}</span>}
				{event.description && <span>{event.description}</span>}
			</div>
			<div className="ctx-events-calendar__modal-actions">
				{recurrenceSeriesId ? <>
					<a className="button button-secondary" href={`/wp-admin/post.php?post=${recurrenceSeriesId}&action=edit`}>{__('Edit recurrence', 'ctx-events')}</a>
					<button type="button" className="button button-secondary" onClick={() => { void apiFetch({ path: `/events/v3/events/${recurrenceSeriesId}/detach-occurrence`, method: 'POST', data: { occurrence_key: event.id } }).then(() => onCancelled(event.id)); }}>{__('Detach occurrence', 'ctx-events')}</button>
				</> : <>
				<a
					className="button button-secondary"
					href={`/wp-admin/post.php?post=${eventId}&action=edit`}
				>
					{__('Edit event', 'ctx-events')}
				</a>
				<button
					type="button"
					className="button-link-delete"
					onClick={() => setIsCancelOpen(true)}
				>
					{__('Cancel event', 'ctx-events')}
				</button>
				</>}
			</div>
			{isCancelOpen && (
				<EventCancelConfirmModal
					action={cancelAction}
					item={calendarEvent}
					onClose={() => setIsCancelOpen(false)}
					onActionPerformed={() => onCancelled(eventId)}
				/>
			)}
		</>
	);
};

const EventCalendarView = ({ filters, scope }: EventCalendarViewProps) => {
	const containerRef = useRef<HTMLDivElement | null>(null);
	const [isLoading, setIsLoading] = useState(false);
	const initialMonth = useMemo(() => getMonthFromScope(scope, []), [scope]);
	const { activeMonth, setActiveMonth } = useStoredCalendarMonth(
		'ctx-events:admin:events:calendar-month',
		initialMonth,
	);
	const { view: storedView, setView: setStoredView } = useStoredCalendarView(
		'ctx-events:admin:events:calendar-view',
	);
	const activeMonthRef = useRef(activeMonth);
	const storedViewRef = useRef(storedView);
	const currentFiltersKey = useMemo(() => filtersKey(filters), [filters]);

	activeMonthRef.current = activeMonth;
	storedViewRef.current = storedView;

	useEffect(() => {
		const container = containerRef.current;
		if (!container) return;

		const timezone = getTimezone();
		const calendars: Record<string, CalendarType> = {};
		const eventModal = createEventModalPlugin();
		const viewStoragePlugin = createViewStoragePlugin(setStoredView);
		const selectedDate = Temporal.PlainDate.from({
			year: activeMonthRef.current.getFullYear(),
			month: activeMonthRef.current.getMonth() + 1,
			day: 1,
		});
		const calendar = createCalendar({
			views: [createViewMonthGrid(), createViewWeek(), createViewYearGrid()],
			defaultView: storedViewRef.current,
			selectedDate,
			locale: getLocale(),
			timezone,
			firstDayOfWeek: 1,
			isResponsive: false,
			calendars,
			monthGridOptions: { nEventsPerDay: 8 },
			callbacks: {
				fetchEvents: async (range) => {
					setIsLoading(true);
					eventModal.close();

					try {
						const params = new URLSearchParams({
							start_date: range.start.toInstant().toString(),
							end_date: range.end.toInstant().toString(),
						});
						const categories = getFilterValue(filters, 'categories');
						if (Array.isArray(categories)) {
							for (const category of categories) {
								params.append('categories', String(category));
							}
						}
						const location = getFilterValue(filters, 'location');
						if (location) params.append('location', String(location));
						const persons = getFilterValue(filters, 'persons');
						if (Array.isArray(persons) && persons.length > 0) {
							params.append('person', String(persons[0]));
						}
						const [events, occurrences] = await Promise.all([
							apiFetch<Array<CalendarEvent>>({
							path: `/events/v3/events/calendar?${params.toString()}`,
							}),
							apiFetch<Array<VirtualOccurrence>>({
								path: '/events/v3/events?with_recurrences=true&scope=1-year&per_page=100',
							}),
						]);
						const virtualEvents = occurrences
							.filter((occurrence) => occurrence.type === 'virtual')
							.filter((occurrence) => {
								const start = new Date(occurrence.startDate).getTime();
								return start >= range.start.toInstant().epochMilliseconds
									&& start < range.end.toInstant().epochMilliseconds;
							})
							.map(toVirtualCalendarEvent);
						const calendarEvents = [...events, ...virtualEvents];

						registerCategoryColors(calendarEvents, calendars);
						return calendarEvents.map((event) => toScheduleEvent(event, timezone));
					} finally {
						setIsLoading(false);
					}
				},
				onClickDate: (date) => {
					window.location.href = getCreateEventUrl(
						new Date(date.year, date.month - 1, date.day),
					);
				},
				onSelectedDateUpdate: (date) => {
					setActiveMonth(new Date(date.year, date.month - 1, 1));
				},
			},
		}, [eventModal, viewStoragePlugin]);
		const customModalRoots = new Map<string, ReturnType<typeof createRoot>>();

		calendar._setCustomComponentFn('eventModal', (element, props) => {
			const event = props['calendarEvent'] as ScheduleEvent | undefined;
			const componentId = element.dataset.ccid;
			if (!event || !componentId) return;

			const root = createRoot(element);
			customModalRoots.set(componentId, root);
			root.render(
				<EventModalActions
					event={event}
					onCancelled={(eventId) => {
						eventModal.close();
						calendar.events.remove(eventId);
					}}
				/>,
			);
		});
		calendar._setDestroyCustomComponentInstance((componentId) => {
			customModalRoots.get(componentId)?.unmount();
			customModalRoots.delete(componentId);
		});

		calendar.render(container);
		return () => {
			calendar.destroy();
			customModalRoots.forEach((root) => root.unmount());
		};
	}, [currentFiltersKey, filters, setActiveMonth, setStoredView]);

	return (
		<div className="ctx-events-calendar">
			<div className="ctx-events-calendar__mount" ref={containerRef} />
			{isLoading && (
				<div className="ctx-events-calendar__loading" role="status">
					<span className="ctx-events-calendar__spinner" aria-hidden="true" />
					{__('Loading calendar…', 'ctx-events')}
				</div>
			)}
		</div>
	);
};

export default EventCalendarView;
