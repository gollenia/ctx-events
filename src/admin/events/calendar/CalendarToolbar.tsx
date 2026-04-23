import { __ } from '@wordpress/i18n';

interface CalendarToolbarProps {
	activeMonth: Date;
	loading: boolean;
	monthLabel: string;
	onPreviousMonth: () => void;
	onCurrentMonth: () => void;
	onNextMonth: () => void;
}

const CalendarToolbar = ({
	loading,
	monthLabel,
	onPreviousMonth,
	onCurrentMonth,
	onNextMonth,
}: CalendarToolbarProps) => {
	return (
		<div className="ctx-events-calendar__toolbar">
			<div className="ctx-events-calendar__actions">
				<button
					type="button"
					className="button"
					disabled={loading}
					onClick={onPreviousMonth}
				>
					{__('Previous month', 'ctx-events')}
				</button>
				<button
					type="button"
					className="button"
					disabled={loading}
					onClick={onCurrentMonth}
				>
					{__('Current month', 'ctx-events')}
				</button>
			</div>
			<h2 className="ctx-events-calendar__title">{monthLabel}</h2>
			<button
				type="button"
				className="button"
				disabled={loading}
				onClick={onNextMonth}
			>
				{__('Next month', 'ctx-events')}
			</button>
		</div>
	);
};

export default CalendarToolbar;
