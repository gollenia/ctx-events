import PanelTitle from '@events/adminfields/PanelTitle';
import { CheckboxControl, Flex, Icon } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import DateTimeFieldRow from './DateTimeFieldRow';
import icons from './icons';
import type { EditorSelection, EventMeta } from './types';

const splitDateTime = (dateTime?: string) => {
	if (!dateTime) {
		return { date: '', time: '' };
	}

	const [date, time] = dateTime.split('T');

	return { date, time: time ? time.slice(0, 5) : '00:00' };
};

const combineDateTime = (date: string, time: string) => {
	if (!date) {
		return '';
	}

	return `${date}T${time || '00:00'}`;
};

const addOneHour = (isoString: string) => {
	if (!isoString) {
		return '';
	}

	const date = new Date(isoString);
	date.setHours(date.getHours() + 1);
	const offset = date.getTimezoneOffset() * 60000;

	return new Date(date.getTime() - offset).toISOString().slice(0, 16);
};

const formatLocalDateTime = (date: Date) => {
	const year = date.getFullYear();
	const month = String(date.getMonth() + 1).padStart(2, '0');
	const day = String(date.getDate()).padStart(2, '0');
	const hours = String(date.getHours()).padStart(2, '0');
	const minutes = String(date.getMinutes()).padStart(2, '0');

	return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const getDefaultEventStart = () => {
	const now = new Date();
	now.setHours(9, 0, 0, 0);

	return formatLocalDateTime(now);
};

const getInitialEventStart = () => {
	const defaultStart = getDefaultEventStart();

	if (typeof window === 'undefined') {
		return defaultStart;
	}

	const params = new URLSearchParams(window.location.search);
	const dateParam = params.get('date');

	if (!dateParam || !/^\d{4}-\d{2}-\d{2}$/.test(dateParam)) {
		return defaultStart;
	}

	return `${dateParam}T09:00`;
};

const DatetimeSelector = () => {
	const postType = useSelect((select) => {
		const editor = select('core/editor') as EditorSelection;
		return editor.getCurrentPostType() ?? '';
	}, []);

	const { lockPostSaving, unlockPostSaving } = useDispatch('core/editor');
	const { createNotice, removeNotice } = useDispatch('core/notices');
	const [rawMeta, setMeta] = useEntityProp('postType', postType, 'meta');
	const meta = (rawMeta ?? {}) as EventMeta;

	const { isSaving, isPublishing, isPublishSidebarOpened } = useSelect(
		(select) => {
			const editor = select('core/editor') as EditorSelection;

			return {
				isSaving: editor.isSavingPost?.() ?? false,
				isPublishing: editor.isPublishingPost?.() ?? false,
				isPublishSidebarOpened: editor.isPublishSidebarOpened?.() ?? false,
			};
		},
		[],
	);

	const hasAttemptedSave = useRef(false);
	const hasInitializedDefaultDate = useRef(false);

	useEffect(() => {
		if (postType !== 'ctx-event') {
			return;
		}

		if (hasInitializedDefaultDate.current) {
			return;
		}

		if (meta._event_start || meta._event_end) {
			hasInitializedDefaultDate.current = true;
			return;
		}

		const defaultStart = getInitialEventStart();

		hasInitializedDefaultDate.current = true;
		setMeta({
			_event_start: defaultStart,
			_event_end: addOneHour(defaultStart),
		});
	}, [meta._event_end, meta._event_start, postType, setMeta]);

	useEffect(() => {
		if (postType !== 'ctx-event') {
			return;
		}

		if (isSaving || isPublishing) {
			hasAttemptedSave.current = true;
		}

		const noticeId = 'event_date_notice';
		const isMissingStart = !meta._event_start;
		const isInvalidRange =
			Boolean(meta._event_start) &&
			Boolean(meta._event_end) &&
			(meta._event_start as string) > (meta._event_end as string);

		if (!isMissingStart && !isInvalidRange) {
			unlockPostSaving('event_date_lock');
			removeNotice(noticeId);
			hasAttemptedSave.current = false;
			return;
		}

		lockPostSaving('event_date_lock');

		if (hasAttemptedSave.current || isPublishSidebarOpened) {
			createNotice(
				'error',
				isMissingStart
					? __('please set a start date before saving.', 'ctx-events')
					: __('The end must be after the start!', 'ctx-events'),
				{
					id: noticeId,
					isDismissible: true,
				},
			);
		}
	}, [
		isPublishSidebarOpened,
		isPublishing,
		isSaving,
		lockPostSaving,
		meta._event_end,
		meta._event_start,
		postType,
		removeNotice,
		unlockPostSaving,
		createNotice,
	]);

	if (postType !== 'ctx-event') {
		return null;
	}

	const start = splitDateTime(meta._event_start);
	const end = splitDateTime(meta._event_end);
	const isAllDay = Boolean(Number(meta._event_all_day ?? 0));

	return (
		<PluginDocumentSettingPanel
			name="events-datetime-settings"
			title={
				<PanelTitle
					icon={<Icon icon={icons.date} />}
					title={__('Event Date & Time', 'ctx-events')}
				/>
			}
		>
			<Flex direction="column" gap="16px">
				<DateTimeFieldRow
					label={__('Start of the event', 'ctx-events')}
					date={start.date}
					time={start.time}
					showTime={!isAllDay}
					onDateChange={(value) => {
						const newStart = combineDateTime(value, start.time);
						const updates: Partial<EventMeta> = {
							_event_start: newStart,
						};
						if (!meta._event_end || newStart >= meta._event_end) {
							updates._event_end = addOneHour(newStart);
						}
						setMeta(updates);
					}}
					onTimeChange={(value) => {
						const newStart = combineDateTime(start.date, value);
						const updates: Partial<EventMeta> = {
							_event_start: newStart,
						};
						if (!meta._event_end || newStart >= meta._event_end) {
							updates._event_end = addOneHour(newStart);
						}
						setMeta(updates);
					}}
				/>
				<DateTimeFieldRow
					label={__('End of the event', 'ctx-events')}
					date={end.date}
					time={end.time}
					showTime={!isAllDay}
					minDate={start.date}
					minTime={
						start.date && end.date === start.date ? start.time : undefined
					}
					onDateChange={(value) =>
						setMeta({ _event_end: combineDateTime(value, end.time) })
					}
					onTimeChange={(value) =>
						setMeta({ _event_end: combineDateTime(end.date, value) })
					}
				/>
				<CheckboxControl
					label={__('All Day', 'ctx-events')}
					__nextHasNoMarginBottom
					checked={isAllDay}
					onChange={(value) => setMeta({ _event_all_day: value ? 1 : 0 })}
				/>
			</Flex>
		</PluginDocumentSettingPanel>
	);
};

export default DatetimeSelector;
