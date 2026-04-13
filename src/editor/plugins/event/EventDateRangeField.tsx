import { Popover, TextControl } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { DateRange } from 'react-day-picker';
import getLocale from '../../../shared/i18n/locale';
import CtxDateRangeCalendar from './CtxDateRangeCalendar';

type Props = {
	start: string;
	end: string;
	onChange: (value: { startDate: string; endDate: string }) => void;
};

const toDate = (value?: string) => {
	if (!value) {
		return undefined;
	}

	const date = new Date(value);

	return Number.isNaN(date.getTime()) ? undefined : date;
};

const toDateOnlyString = (value?: Date) => {
	if (!value) {
		return '';
	}

	const year = value.getFullYear();
	const month = String(value.getMonth() + 1).padStart(2, '0');
	const day = String(value.getDate()).padStart(2, '0');

	return `${year}-${month}-${day}`;
};

const formatIsoInputValue = (start?: string, end?: string) => {
	if (!start) {
		return '';
	}

	if (!end || end === start) {
		return start;
	}

	return `${start} - ${end}`;
};

const formatLocalizedDate = (value?: string) => {
	if (!value) {
		return '';
	}

	const date = toDate(value);

	if (!date) {
		return value;
	}

	return new Intl.DateTimeFormat(getLocale(), {
		year: 'numeric',
		month: '2-digit',
		day: '2-digit',
	}).format(date);
};

const formatLocalizedInputValue = (start?: string, end?: string) => {
	if (!start) {
		return '';
	}

	const localizedStart = formatLocalizedDate(start);

	if (!end || end === start) {
		return localizedStart;
	}

	return `${localizedStart} - ${formatLocalizedDate(end)}`;
};

const parseInputValue = (value: string) => {
	const trimmed = value.trim();

	if (trimmed === '') {
		return { startDate: '', endDate: '' };
	}

	const match = trimmed.match(
		/^(\d{4}-\d{2}-\d{2})(?:\s+-\s+(\d{4}-\d{2}-\d{2}))?$/,
	);

	if (!match) {
		return null;
	}

	const [, startDate, endDate] = match;

	return {
		startDate,
		endDate: endDate ?? startDate,
	};
};

const EventDateRangeField = ({ start, end, onChange }: Props) => {
	const [inputValue, setInputValue] = useState(
		formatLocalizedInputValue(start, end),
	);
	const [isOpen, setIsOpen] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const anchorRef = useRef<HTMLDivElement | null>(null);
	const [draftRange, setDraftRange] = useState<DateRange | undefined>(
		undefined,
	);

	useEffect(() => {
		setInputValue(
			isOpen
				? formatIsoInputValue(start, end)
				: formatLocalizedInputValue(start, end),
		);
		setError(null);
	}, [end, isOpen, start]);

	const committedRange: DateRange | undefined = useMemo(() => {
		const from = toDate(start);
		const to = toDate(end || start);

		if (!from && !to) {
			return undefined;
		}

		return {
			from,
			to,
		};
	}, [end, start]);

	useEffect(() => {
		if (!isOpen) {
			setDraftRange(committedRange);
		}
	}, [committedRange, isOpen]);

	const commitInputValue = () => {
		const parsed = parseInputValue(inputValue);

		if (!parsed) {
			setError(__('Use YYYY-MM-DD or YYYY-MM-DD - YYYY-MM-DD.', 'ctx-events'));
			return;
		}

		onChange(parsed);
		setError(null);
	};

	const closePicker = () => {
		setDraftRange(committedRange);
		setInputValue(formatLocalizedInputValue(start, end));
		setError(null);
		setIsOpen(false);
	};

	const confirmPicker = () => {
		if (draftRange?.from) {
			const startDate = toDateOnlyString(draftRange.from);
			const endDate = toDateOnlyString(draftRange.to ?? draftRange.from);

			onChange({ startDate, endDate });
			setInputValue(formatLocalizedInputValue(startDate, endDate));
			setError(null);
			setIsOpen(false);
			return;
		}

		commitInputValue();
		setIsOpen(false);
	};

	return (
		<div className="ctx-event-date-field" ref={anchorRef}>
			<TextControl
				label={__('Date', 'ctx-events')}
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				value={inputValue}
				help={
					error ??
					__(
						'Click to pick a range or type YYYY-MM-DD - YYYY-MM-DD while editing.',
						'ctx-events',
					)
				}
				onFocus={() => {
					setInputValue(formatIsoInputValue(start, end));
					setIsOpen(true);
				}}
				onClick={() => {
					setInputValue(formatIsoInputValue(start, end));
					setIsOpen(true);
				}}
				onChange={(value) => {
					setInputValue(value);
					setError(null);
				}}
				onKeyDown={(event) => {
					if (event.key === 'Enter') {
						event.preventDefault();
						commitInputValue();
						setIsOpen(false);
					}
				}}
			/>
			{isOpen ? (
				<Popover
					anchor={anchorRef.current}
					placement="left"
					flip={false}
					resize={false}
					shift={false}
					onClose={() => setIsOpen(false)}
					className="ctx-event-date-field__popover"
				>
					<div className="ctx-event-range-picker">
						<CtxDateRangeCalendar
							numberOfMonths={2}
							selected={draftRange ?? committedRange}
							defaultMonth={(draftRange ?? committedRange)?.from}
							onSelect={(range) => {
								const startDate = toDateOnlyString(range?.from);
								const endDate = toDateOnlyString(range?.to);

								setDraftRange(range);
								setInputValue(formatIsoInputValue(startDate, endDate));
								setError(null);
							}}
						/>
						<div className="ctx-event-range-picker__actions">
							<button
								type="button"
								className="components-button is-tertiary"
								onClick={closePicker}
							>
								{__('Cancel', 'ctx-events')}
							</button>
							<button
								type="button"
								className="components-button is-primary"
								onClick={confirmPicker}
							>
								{__('OK', 'ctx-events')}
							</button>
						</div>
					</div>
				</Popover>
			) : null}
		</div>
	);
};

export default EventDateRangeField;
