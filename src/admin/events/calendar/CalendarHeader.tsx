import { getLocale } from '@events/i18n';
import {
	Button,
	Flex,
	FlexItem,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { chevronLeft, chevronRight } from '@wordpress/icons';
import type { CalendarView } from './useStoredCalendarView';

type CalendarDate = Temporal.PlainDate | Temporal.ZonedDateTime;

interface Signal<T> {
	value: T;
	subscribe: (listener: (value: T) => void) => () => void;
}

interface ScheduleXCalendarApp {
	datePickerState: {
		selectedDate: Signal<CalendarDate>;
	};
	calendarState: {
		view: Signal<CalendarView>;
		setView: (view: CalendarView, date: CalendarDate) => void;
	};
	config: {
		timezone: Signal<string>;
		views: Signal<
			Array<{
				name: CalendarView;
				backwardForwardUnits: number;
				backwardForwardFn: (date: CalendarDate, amount: number) => CalendarDate;
			}>
		>;
	};
}

interface CalendarHeaderProps {
	calendarApp: ScheduleXCalendarApp;
}

const viewLabels: Record<CalendarView, string> = {
	'month-grid': __('Month', 'ctx-events'),
	week: __('Week', 'ctx-events'),
	'year-grid': __('Year', 'ctx-events'),
};

const toPlainDate = (date: CalendarDate): Temporal.PlainDate =>
	date instanceof Temporal.ZonedDateTime ? date.toPlainDate() : date;

const CalendarHeader = ({ calendarApp }: CalendarHeaderProps) => {
	const [selectedDate, setSelectedDate] = useState(
		calendarApp.datePickerState.selectedDate.value,
	);
	const [view, setView] = useState(calendarApp.calendarState.view.value);

	useEffect(
		() => calendarApp.datePickerState.selectedDate.subscribe(setSelectedDate),
		[calendarApp],
	);
	useEffect(
		() => calendarApp.calendarState.view.subscribe(setView),
		[calendarApp],
	);

	const date = toPlainDate(selectedDate);
	const heading = date.toLocaleString(getLocale(), {
		month: 'long',
		year: 'numeric',
	});
	const selectDate = (nextDate: string): void => {
		if (!nextDate) return;

		calendarApp.datePickerState.selectedDate.value =
			Temporal.PlainDate.from(nextDate);
	};
	const navigate = (direction: 1 | -1): void => {
		const currentView = calendarApp.config.views.value.find(
			(candidate) => candidate.name === view,
		);
		if (!currentView) return;

		calendarApp.datePickerState.selectedDate.value =
			currentView.backwardForwardFn(
				calendarApp.datePickerState.selectedDate.value,
				direction * currentView.backwardForwardUnits,
			);
	};

	return (
		<Flex
			align="center"
			className="ctx-events-calendar__header"
			gap={3}
			justify="space-between"
		>
			<FlexItem isBlock>
				<div className="ctx-events-calendar__header-navigation">
					<Button
						__next40pxDefaultSize
						variant="secondary"
						onClick={() =>
							selectDate(
								Temporal.Now.plainDateISO(
									calendarApp.config.timezone.value,
								).toString(),
							)
						}
					>
						{__('Today', 'ctx-events')}
					</Button>
					<div className="ctx-events-calendar__header-arrows">
						<Button
							__next40pxDefaultSize
							icon={chevronLeft}
							label={__('Previous period', 'ctx-events')}
							onClick={() => navigate(-1)}
						/>
						<Button
							__next40pxDefaultSize
							icon={chevronRight}
							label={__('Next period', 'ctx-events')}
							onClick={() => navigate(1)}
						/>
					</div>
				</div>
			</FlexItem>
			<FlexItem>
				<strong className="ctx-events-calendar__header-heading">
					{heading}
				</strong>
			</FlexItem>
			<FlexItem isBlock>
				<div className="ctx-events-calendar__header-selection">
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						hideLabelFromVision
						label={__('Date', 'ctx-events')}
						onChange={selectDate}
						type="date"
						value={date.toString()}
					/>
					<SelectControl<CalendarView>
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						hideLabelFromVision
						label={__('View', 'ctx-events')}
						onChange={(nextView) =>
							calendarApp.calendarState.setView(
								nextView,
								calendarApp.datePickerState.selectedDate.value,
							)
						}
						options={(Object.keys(viewLabels) as Array<CalendarView>).map(
							(candidate) => ({
								label: viewLabels[candidate],
								value: candidate,
							}),
						)}
						value={view}
					/>
				</div>
			</FlexItem>
		</Flex>
	);
};

export type { ScheduleXCalendarApp };
export default CalendarHeader;
