import {
	type ComponentProps,
	useEffect,
	useMemo,
	useState,
} from '@wordpress/element';
import { type DateRange, DayPicker, type Modifiers } from 'react-day-picker';
import getLocale from '../../../shared/i18n/locale';

type Props = {
	selected?: DateRange;
	onSelect?: (range: DateRange | undefined) => void;
	numberOfMonths?: number;
	defaultMonth?: Date;
};

const clampNumberOfMonths = (numberOfMonths: number) =>
	Math.min(3, Math.max(1, numberOfMonths));

const usePreviewRange = ({
	selected,
	hoveredDate,
}: {
	selected?: DateRange;
	hoveredDate?: Date;
}): DateRange | undefined => {
	return useMemo(() => {
		if (!hoveredDate || !selected?.from || selected.to) {
			return undefined;
		}

		if (hoveredDate < selected.from) {
			return {
				from: hoveredDate,
				to: selected.from,
			};
		}

		if (hoveredDate > selected.from) {
			return {
				from: selected.to ?? selected.from,
				to: hoveredDate,
			};
		}

		return undefined;
	}, [hoveredDate, selected]);
};

const classNames = {
	root: 'components-calendar rdp-root',
	months: 'components-calendar__months rdp-months',
	month: 'rdp-month',
	month_grid: 'components-calendar__month-grid rdp-month_grid',
	month_caption: 'components-calendar__month-caption rdp-month_caption',
	caption_label: 'components-calendar__caption-label rdp-caption_label',
	nav: 'components-calendar__nav rdp-nav',
	button_previous: 'components-calendar__button-previous rdp-button_previous',
	button_next: 'components-calendar__button-next rdp-button_next',
	chevron: 'components-calendar__chevron rdp-chevron',
	weekday: 'components-calendar__weekday rdp-weekday',
	week: 'rdp-week',
	day: 'components-calendar__day rdp-day',
	day_button: 'components-calendar__day-button rdp-day_button',
	selected: 'components-calendar__day--selected rdp-selected',
	today: 'components-calendar__day--today rdp-today',
	disabled: 'components-calendar__day--disabled rdp-disabled',
	range_start: 'components-calendar__range-start rdp-range_start',
	range_end: 'components-calendar__range-end rdp-range_end',
	range_middle: 'components-calendar__range-middle rdp-range_middle',
};

const modifiersClassNames = {
	preview: 'components-calendar__day--preview',
	preview_start: 'components-calendar__day--preview-start',
	preview_end: 'components-calendar__day--preview-end',
};

type DayPickerLocale = NonNullable<
	ComponentProps<typeof DayPicker>['locale']
>;

const normalizeLocale = (locale: string): string => {
	const lower = locale.toLowerCase();

	if (lower.startsWith('de-at')) {
		return 'de-AT';
	}

	if (lower.startsWith('de')) {
		return 'de';
	}

	if (lower.startsWith('en-gb')) {
		return 'en-GB';
	}

	if (lower.startsWith('fr')) {
		return 'fr';
	}

	if (lower.startsWith('it')) {
		return 'it';
	}

	return 'en-US';
};

const localeLoaders: Record<string, () => Promise<DayPickerLocale>> = {
	'de-AT': () =>
		import('react-day-picker/locale/de-AT').then((mod) => mod.deAT),
	de: () => import('react-day-picker/locale/de').then((mod) => mod.de),
	'en-GB': () =>
		import('react-day-picker/locale/en-GB').then((mod) => mod.enGB),
	'en-US': () =>
		import('react-day-picker/locale/en-US').then((mod) => mod.enUS),
	fr: () => import('react-day-picker/locale/fr').then((mod) => mod.fr),
	it: () => import('react-day-picker/locale/it').then((mod) => mod.it),
};

const CtxDateRangeCalendar = ({
	selected,
	onSelect,
	numberOfMonths = 2,
	defaultMonth,
}: Props) => {
	const [hoveredDate, setHoveredDate] = useState<Date | undefined>(undefined);
	const [calendarLocale, setCalendarLocale] = useState<DayPickerLocale | undefined>(
		undefined,
	);
	const previewRange = usePreviewRange({ selected, hoveredDate });

	const modifiers: Modifiers = useMemo(
		() => ({
			preview: previewRange,
			preview_start: previewRange?.from,
			preview_end: previewRange?.to,
		}),
		[previewRange],
	);

	useEffect(() => {
		let isMounted = true;
		const localeKey = normalizeLocale(getLocale());
		const loadLocale = localeLoaders[localeKey] ?? localeLoaders['en-US'];

		loadLocale().then((locale) => {
			if (isMounted) {
				setCalendarLocale(locale);
			}
		});

		return () => {
			isMounted = false;
		};
	}, []);

	return (
		<DayPicker
			mode="range"
			resetOnSelect
			role="application"
			showOutsideDays={false}
			showWeekNumber={false}
			fixedWeeks={false}
			captionLayout="label"
			defaultMonth={defaultMonth}
			numberOfMonths={clampNumberOfMonths(numberOfMonths)}
			selected={selected}
			onSelect={onSelect}
			onDayMouseEnter={(date) => setHoveredDate(date)}
			onDayMouseLeave={() => setHoveredDate(undefined)}
			modifiers={modifiers}
			modifiersClassNames={modifiersClassNames}
			classNames={classNames}
			locale={calendarLocale}
		/>
	);
};

export default CtxDateRangeCalendar;
