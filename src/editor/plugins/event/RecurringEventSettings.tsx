import { Button, CheckboxControl, Flex, FlexBlock, __experimentalNumberControl as NumberControl, SelectControl, TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { EditorSelection } from './types';
import './RecurringEventSettings.scss';
import RecurringEventExceptionsModal, { type RecurrenceException } from './RecurringEventExceptionsModal';

type Frequency = 'daily' | 'weekly' | 'monthly' | 'yearly';
type Weekday = 'monday' | 'tuesday' | 'wednesday' | 'thursday' | 'friday' | 'saturday' | 'sunday';
type Meta = {
	_recurrence_first_occurrence_starts_at?: unknown;
	_recurrence_occurrence_duration_seconds?: unknown;
	_recurrence_timezone?: unknown;
	_recurrence_frequency?: unknown;
	_recurrence_interval?: unknown;
	_recurrence_weekdays?: unknown;
	_recurrence_monthday?: unknown;
	_recurrence_weekday_position?: unknown;
	_recurrence_ends_on?: unknown;
	_recurrence_exceptions?: unknown;
};

const weekdays: Array<{ value: Weekday; label: string }> = [
	{ value: 'monday', label: __('Monday', 'ctx-events') }, { value: 'tuesday', label: __('Tuesday', 'ctx-events') },
	{ value: 'wednesday', label: __('Wednesday', 'ctx-events') }, { value: 'thursday', label: __('Thursday', 'ctx-events') },
	{ value: 'friday', label: __('Friday', 'ctx-events') }, { value: 'saturday', label: __('Saturday', 'ctx-events') },
	{ value: 'sunday', label: __('Sunday', 'ctx-events') },
];

const defaultStart = (): string => {
	return new Date().toISOString();
};

const toDateTimeInput = (value: unknown): string => {
	if (typeof value !== 'string' || value === '') return '';
	const date = new Date(value);
	if (Number.isNaN(date.getTime())) return '';
	return new Date(date.getTime() - date.getTimezoneOffset() * 60_000).toISOString().slice(0, 16);
};

const toStoredDateTime = (value: string): string => value === '' ? '' : new Date(value).toISOString();

const selectedWeekdays = (value: unknown): Weekday[] => Array.isArray(value)
	? value.filter((item): item is Weekday => weekdays.some((weekday) => weekday.value === item)) : [];

const recurrenceExceptions = (value: unknown): Record<string, RecurrenceException> => {
	if (!value || typeof value !== 'object' || Array.isArray(value)) return {};
	return Object.fromEntries(Object.entries(value).filter(([, exception]) => exception !== null && typeof exception === 'object')) as Record<string, RecurrenceException>;
};

const RecurringEventSettings = () => {
	const postType = useSelect((select) => (select('core/editor') as EditorSelection).getCurrentPostType() ?? '', []);
	const postId = useSelect((select) => (select('core/editor') as EditorSelection).getCurrentPost()?.id ?? 0, []);
	const [rawMeta, setMeta] = useEntityProp('postType', postType, 'meta');
	const [showExceptions, setShowExceptions] = useState(false);
	const meta = (rawMeta ?? {}) as Meta;
	const frequency = (meta._recurrence_frequency as Frequency | undefined) ?? 'weekly';
	const days = selectedWeekdays(meta._recurrence_weekdays);
	const monthlyByWeekday = frequency === 'monthly' && meta._recurrence_weekday_position !== undefined;
	const exceptions = recurrenceExceptions(meta._recurrence_exceptions);

	useEffect(() => {
		if (postType !== 'ctx-event-recurring' || meta._recurrence_first_occurrence_starts_at) return;
		setMeta({ _recurrence_first_occurrence_starts_at: defaultStart(), _recurrence_occurrence_duration_seconds: 3600, _recurrence_timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC', _recurrence_frequency: 'weekly', _recurrence_interval: 1, _recurrence_weekdays: ['monday'] });
	}, [meta._recurrence_first_occurrence_starts_at, postType, setMeta]);

	if (postType !== 'ctx-event-recurring') return null;

	const setFrequency = (next: Frequency): void => setMeta({
		_recurrence_frequency: next,
		_recurrence_weekdays: next === 'weekly' ? (days.length ? days : ['monday']) : undefined,
		_recurrence_monthday: next === 'monthly' ? 1 : undefined,
		_recurrence_weekday_position: undefined,
	});
	const toggleDay = (day: Weekday, checked: boolean): void => setMeta({ _recurrence_weekdays: checked ? [...days, day] : days.filter((item) => item !== day) });

	return <PluginDocumentSettingPanel name="ctx-recurring-event-settings" title={__('Recurrence', 'ctx-events')}>
		<div className="ctx-recurring-event-settings">
			<TextControl label={__('First occurrence', 'ctx-events')} type="datetime-local" value={toDateTimeInput(meta._recurrence_first_occurrence_starts_at)} onChange={(value) => setMeta({ _recurrence_first_occurrence_starts_at: toStoredDateTime(value) })} />
			<Flex className="ctx-recurring-event-settings__row" gap="8px" align="flex-start">
				<FlexBlock><NumberControl label={__('Duration in minutes', 'ctx-events')} min={1} value={String(Number(meta._recurrence_occurrence_duration_seconds ?? 3600) / 60)} onChange={(value) => setMeta({ _recurrence_occurrence_duration_seconds: Number(value ?? 0) * 60 })} /></FlexBlock>
				<FlexBlock><NumberControl label={__('Every', 'ctx-events')} min={1} value={String(meta._recurrence_interval ?? 1)} onChange={(value) => setMeta({ _recurrence_interval: Number(value ?? 0) })} /></FlexBlock>
			</Flex>
			<TextControl label={__('Timezone', 'ctx-events')} value={(meta._recurrence_timezone as string | undefined) ?? ''} onChange={(value) => setMeta({ _recurrence_timezone: value })} help={__('Use an IANA timezone, e.g. Europe/Vienna.', 'ctx-events')} />
			<SelectControl label={__('Frequency', 'ctx-events')} value={frequency} onChange={(value) => setFrequency(value as Frequency)} options={[{ label: __('Daily', 'ctx-events'), value: 'daily' }, { label: __('Weekly', 'ctx-events'), value: 'weekly' }, { label: __('Monthly', 'ctx-events'), value: 'monthly' }, { label: __('Yearly', 'ctx-events'), value: 'yearly' }]} />
			{frequency === 'weekly' && <div className="ctx-recurring-event-settings__weekdays" role="group" aria-label={__('Weekdays', 'ctx-events')}>{weekdays.map((weekday) => <CheckboxControl key={weekday.value} className="ctx-recurring-event-settings__weekday" label={weekday.label} checked={days.includes(weekday.value)} onChange={(checked) => toggleDay(weekday.value, checked)} />)}</div>}
			{frequency === 'monthly' && <div className="ctx-recurring-event-settings__monthly"><SelectControl label={__('Monthly rule', 'ctx-events')} value={monthlyByWeekday ? 'weekday' : 'monthday'} onChange={(value) => setMeta(value === 'weekday' ? { _recurrence_monthday: undefined, _recurrence_weekday_position: 1, _recurrence_weekdays: ['monday'] } : { _recurrence_monthday: 1, _recurrence_weekday_position: undefined, _recurrence_weekdays: undefined })} options={[{ label: __('Day of month', 'ctx-events'), value: 'monthday' }, { label: __('Weekday position', 'ctx-events'), value: 'weekday' }]} />
				{monthlyByWeekday ? <Flex className="ctx-recurring-event-settings__row" gap="8px" align="flex-start"><FlexBlock><SelectControl label={__('Position', 'ctx-events')} value={String(meta._recurrence_weekday_position ?? 1) as '1' | '2' | '3' | '4' | '5' | '-1'} onChange={(value) => setMeta({ _recurrence_weekday_position: Number(value) })} options={[{ label: __('First', 'ctx-events'), value: '1' }, { label: __('Second', 'ctx-events'), value: '2' }, { label: __('Third', 'ctx-events'), value: '3' }, { label: __('Fourth', 'ctx-events'), value: '4' }, { label: __('Fifth', 'ctx-events'), value: '5' }, { label: __('Last', 'ctx-events'), value: '-1' }]} /></FlexBlock><FlexBlock><SelectControl label={__('Weekday', 'ctx-events')} value={days[0] ?? 'monday'} onChange={(value) => setMeta({ _recurrence_weekdays: [value] })} options={weekdays.map((weekday) => ({ label: weekday.label, value: weekday.value }))} /></FlexBlock></Flex> : <NumberControl label={__('Day of month', 'ctx-events')} min={1} max={31} value={String(meta._recurrence_monthday ?? 1)} onChange={(value) => setMeta({ _recurrence_monthday: Number(value ?? 0) })} />}</div>}
			<TextControl label={__('Ends on', 'ctx-events')} type="date" value={(meta._recurrence_ends_on as string | undefined) ?? ''} onChange={(value) => setMeta({ _recurrence_ends_on: value || undefined })} help={__('Optional. Leave empty for an open-ended series.', 'ctx-events')} />
			<Button variant="secondary" onClick={() => setShowExceptions(true)} disabled={!postId}>{__('Manage exceptions', 'ctx-events')}</Button>
		</div>
		<RecurringEventExceptionsModal postId={postId} exceptions={exceptions} show={showExceptions} onClose={() => setShowExceptions(false)} onChange={(nextExceptions) => setMeta({ _recurrence_exceptions: nextExceptions })} />
	</PluginDocumentSettingPanel>;
};

export default RecurringEventSettings;
