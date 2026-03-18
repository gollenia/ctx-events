import {
	CheckboxControl,
	__experimentalNumberControl as NumberControl,
	PanelRow,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

import './datetime.scss';
import type { EditorSelection, EventMeta } from './types';

const dayOptions = [
	{ label: __('Sunday', 'ctx-events'), value: '0' },
	{ label: __('Monday', 'ctx-events'), value: '1' },
	{ label: __('Tuesday', 'ctx-events'), value: '2' },
	{ label: __('Wednesday', 'ctx-events'), value: '3' },
	{ label: __('Thursday', 'ctx-events'), value: '4' },
	{ label: __('Friday', 'ctx-events'), value: '5' },
	{ label: __('Saturday', 'ctx-events'), value: '6' },
];

const getNow = () => {
	return new Date().toISOString().split('T')[0];
};

const addOneDay = (date: string) => {
	const nextDate = new Date(date);
	nextDate.setDate(nextDate.getDate() + 1);
	return nextDate.toISOString().split('T')[0];
};

const RecurrenceSettings = () => {
	const postType = useSelect((select) => {
		const editor = select('core/editor') as EditorSelection;
		return editor.getCurrentPostType() ?? '';
	}, []);

	const [rawMeta, setMeta] = useEntityProp('postType', postType, 'meta');
	const meta = (rawMeta ?? {}) as EventMeta;

	if (postType !== 'event-recurring') {
		return null;
	}

	const selectedDays = meta._recurrence_byday
		? meta._recurrence_byday.split(',')
		: [];

	const toggleDay = (day: string) => {
		const nextDays = selectedDays.includes(day)
			? selectedDays.filter((value) => value !== day)
			: [...selectedDays, day];

		setMeta({ _recurrence_byday: nextDays.join(',') });
	};

	return (
		<>
			<PluginDocumentSettingPanel
				name="events-datetime-settings"
				title={__('Time and Date', 'ctx-events')}
				className="events-datetime-settings"
			>
				<div className="em-date-row">
					<h3>{__('Date', 'ctx-events')}</h3>
					<PanelRow>
						<label htmlFor="em-from-date">{__('First time', 'ctx-events')}</label>
						<div>
							<TextControl
								value={meta._event_start_date ?? getNow()}
								onChange={(value) => {
									const updates: Partial<EventMeta> = {
										_event_start_date: value,
									};
									if (!meta._event_end_date || meta._event_end_date < value) {
										updates._event_end_date = addOneDay(value);
									}
									setMeta(updates);
								}}
								id="em-from-date"
								type="date"
								className="em-date-input"
							/>
						</div>
					</PanelRow>
					<PanelRow>
						<label htmlFor="em-to-date">{__('Last time', 'ctx-events')}</label>
						<div>
							<TextControl
								value={meta._event_end_date ?? ''}
								onChange={(value) => {
									setMeta({ _event_end_date: value });
								}}
								id="em-to-date"
								min={meta._event_start_date}
								type="date"
								className="em-date-input"
							/>
						</div>
					</PanelRow>
				</div>
				<h3>{__('Time', 'ctx-events')}</h3>
				<PanelRow className="em-time-row">
					<TextControl
						className="em-time-input"
						value={meta._event_start_time ?? ''}
						onChange={(value) => {
							setMeta({ _event_start_time: value });
						}}
						label={__('Start', 'ctx-events')}
						disabled={Boolean(Number(meta._event_all_day ?? 0))}
						type="time"
					/>

					<TextControl
						className="em-time-input"
						value={meta._event_end_time ?? ''}
						onChange={(value) => {
							setMeta({ _event_end_time: value });
						}}
						disabled={Boolean(Number(meta._event_all_day ?? 0))}
						label={__('End', 'ctx-events')}
						type="time"
					/>
				</PanelRow>

				<CheckboxControl
					checked={Boolean(Number(meta._event_all_day ?? 0))}
					onChange={(value) => {
						setMeta({ _event_all_day: value ? 1 : 0 });
					}}
					label={__('All day', 'ctx-events')}
				/>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="events-recurrence-settings"
				title={__('Recurrence', 'ctx-events')}
				className="events-recurrence-settings"
			>
				<SelectControl
					label={__('Recurring', 'ctx-events')}
					options={[
						{ label: __('None', 'ctx-events'), value: '' },
						{ label: __('Daily', 'ctx-events'), value: 'daily' },
						{ label: __('Weekly', 'ctx-events'), value: 'weekly' },
						{ label: __('Monthly', 'ctx-events'), value: 'monthly' },
						{ label: __('Yearly', 'ctx-events'), value: 'yearly' },
					]}
					value={meta._recurrence_freq ?? ''}
					onChange={(value) => {
						setMeta({ _recurrence_freq: value });
					}}
				/>

				{meta._recurrence_freq === 'weekly' ? (
					<div className="mt-4">
						{dayOptions.map((day) => (
							<CheckboxControl
								key={day.value}
								checked={selectedDays.includes(day.value)}
								onChange={() => {
									toggleDay(day.value);
								}}
								label={day.label}
							/>
						))}
					</div>
				) : null}
				<NumberControl
					className="mt-4"
					label={__('Interval', 'ctx-events')}
					value={meta._recurrence_interval ?? 1}
					min={1}
					onChange={(value) => {
						setMeta({ _recurrence_interval: value ?? 1 });
					}}
				/>
				{meta._recurrence_freq === 'monthly' ? (
					<PanelRow className="mt-4">
						<SelectControl
							label={__('Every', 'ctx-events')}
							options={[
								{ label: __('First', 'ctx-events'), value: '1' },
								{ label: __('Second', 'ctx-events'), value: '2' },
								{ label: __('Third', 'ctx-events'), value: '3' },
								{ label: __('Fourth', 'ctx-events'), value: '4' },
								{ label: __('Fifth', 'ctx-events'), value: '5' },
								{ label: __('Last', 'ctx-events'), value: '-1' },
							]}
							value={meta._recurrence_byweekno ?? ''}
							onChange={(value) => {
								setMeta({ _recurrence_byweekno: value });
							}}
						/>

						<SelectControl
							label={__('Weekday', 'ctx-events')}
							options={dayOptions}
							value={meta._recurrence_byday ?? ''}
							onChange={(value) => {
								setMeta({ _recurrence_byday: value });
							}}
						/>
					</PanelRow>
				) : null}
			</PluginDocumentSettingPanel>
		</>
	);
};

export default RecurrenceSettings;
