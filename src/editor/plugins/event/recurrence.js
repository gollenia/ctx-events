/**
 * Adds a metabox for the page color settings
 */

/**
 * WordPress dependencies
 */
import {
	CheckboxControl,
	__experimentalNumberControl as NumberControl,
	PanelRow,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { select } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';

import './datetime.scss';

import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

const datetimeSelector = () => {
	const postType = select('core/editor').getCurrentPostType();

	if (postType !== 'event-recurring') return null;

	const [meta, setMeta] = useEntityProp('postType', postType, 'meta');

	const getNow = () => {
		const endDate = new Date();
		return endDate.toISOString().split('T')[0];
	};

	const toggleDay = (day) => {
		let days = meta._recurrence_byday ? meta._recurrence_byday.split(',') : [];

		if (days.includes(day))
			days = days.filter((value, index, arr) => value != day);
		else days.push(day);
		setMeta({ _recurrence_byday: days.join(',') });
	};

	const dayArray = [
		{ label: __('Sun', 'ctx-events'), value: '0' },
		{ label: __('Mon', 'ctx-events'), value: '1' },
		{ label: __('Tue', 'ctx-events'), value: '2' },
		{ label: __('Wed', 'ctx-events'), value: '3' },
		{ label: __('Thu', 'ctx-events'), value: '4' },
		{ label: __('Fri', 'ctx-events'), value: '5' },
		{ label: __('Sat', 'ctx-events'), value: '6' },
	];

	const longDayArray = [
		{ label: __('Sunday', 'ctx-events'), value: '0' },
		{ label: __('Monday', 'ctx-events'), value: '1' },
		{ label: __('Tuesday', 'ctx-events'), value: '2' },
		{ label: __('Wednesday', 'ctx-events'), value: '3' },
		{ label: __('Thursday', 'ctx-events'), value: '4' },
		{ label: __('Friday', 'ctx-events'), value: '5' },
		{ label: __('Saturday', 'ctx-events'), value: '6' },
	];

	const addOneDay = (date) => {
		const newDate = new Date(date);
		newDate.setDate(newDate.getDate() + 1);

		return newDate.toISOString().split('T')[0];
	};

	const minEndDate = meta._event_start_date
		? addOneDay(meta._event_start_date)
		: getNow();

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
						<label for="em-from-date">{__('First time', 'ctx-events')}</label>
						<div>
							<TextControl
								value={
									meta._event_start_date ? meta._event_start_date : getNow()
								}
								onChange={(value) => {
									setMeta({ _event_start_date: value });
									if (!meta._event_end_date || meta._event_end_date < value) {
										setMeta({ _event_end_date: addOneDay(value) });
									}
								}}
								name="em-from-date"
								type="date"
								className="em-date-input"
							/>
						</div>
					</PanelRow>
					<PanelRow>
						<label for="em-to-date">{__('Last time', 'ctx-events')}</label>
						<div>
							<TextControl
								value={meta._event_end_date}
								onChange={(value) => {
									setMeta({ _event_end_date: value });
								}}
								name="em-to-date"
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
						value={meta._event_start_time}
						onChange={(value) => {
							setMeta({ _event_start_time: value });
						}}
						label={__('Start', 'ctx-events')}
						disabled={meta._event_all_day}
						type="time"
					/>

					<TextControl
						className="em-time-input"
						value={meta._event_end_time}
						onChange={(value) => {
							setMeta({ _event_end_time: value });
						}}
						min={minEndDate}
						disabled={meta._event_all_day}
						label={__('End', 'ctx-events')}
						type="time"
					/>
				</PanelRow>

				<CheckboxControl
					checked={meta._event_all_day == 1}
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
					value={meta._recurrence_freq}
					onChange={(value) => {
						setMeta({ _recurrence_freq: value });
					}}
				/>

				{meta._recurrence_freq == 'weekly' && (
					<div className="mt-4">
						{longDayArray.map((day, index) => (
							<CheckboxControl
								key={index}
								checked={meta._recurrence_byday.includes(day.value)}
								onChange={(value) => {
									toggleDay(day.value);
								}}
								label={day.label}
							/>
						))}
					</div>
				)}
				<NumberControl
					className="mt-4"
					label={__('Interval', 'ctx-events')}
					value={meta._recurrence_interval}
					min={1}
					onChange={(value) => {
						setMeta({ _recurrence_interval: value });
					}}
				/>
				{meta._recurrence_freq == 'monthly' && (
					<>
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
								value={meta._recurrence_byweekno}
								onChange={(value) => {
									setMeta({ _recurrence_byweekno: value });
								}}
							/>

							<SelectControl
								label={__('Weekday', 'ctx-events')}
								options={longDayArray}
								value={meta._recurrence_byday}
								onChange={(value) => {
									setMeta({ _recurrence_byday: value });
								}}
							/>
						</PanelRow>
					</>
				)}
			</PluginDocumentSettingPanel>
		</>
	);
};

export default datetimeSelector;
