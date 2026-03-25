import {
	CheckboxControl,
	Flex,
	FlexItem,
	Icon,
	TextControl,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
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
				<Flex align="center" gap="0.5rem" justify="flex-start">
					<Icon
						icon={icons.date}
						width={20}
						height={20}
						color="rgb(117, 117, 117)"
					/>
					{__('Date and Time', 'ctx-events')}
				</Flex>
			}
		>
			<Flex direction="column" gap="16px">
				<div>
					<p
						style={{
							marginBottom: '8px',
							fontSize: '11px',
							textTransform: 'uppercase',
						}}
					>
						<strong>{__('Start of the event', 'ctx-events')}</strong>
					</p>
					<Flex>
						<FlexItem isBlock>
							<TextControl
								type="date"
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								value={start.date}
								onChange={(value) => {
									const newStart = combineDateTime(value, start.time);
									const updates: Partial<EventMeta> = {
										_event_start: newStart,
									};
									if (!meta._event_end || newStart >= meta._event_end) {
										updates._event_end = addOneHour(newStart);
									}
									setMeta(updates);
								}}
							/>
						</FlexItem>
						{!isAllDay ? (
							<FlexItem>
								<TextControl
									type="time"
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									value={start.time}
									onChange={(value) => {
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
							</FlexItem>
						) : null}
					</Flex>
				</div>
				<div>
					<p
						style={{
							marginTop: '10px',
							marginBottom: '8px',
							fontSize: '11px',
							textTransform: 'uppercase',
						}}
					>
						<strong>{__('End of the event', 'ctx-events')}</strong>
					</p>
					<Flex>
						<FlexItem isBlock>
							<TextControl
								type="date"
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								value={end.date}
								min={start.date}
								onChange={(value) =>
									setMeta({ _event_end: combineDateTime(value, end.time) })
								}
							/>
						</FlexItem>
						{!isAllDay ? (
							<FlexItem>
								<TextControl
									type="time"
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									value={end.time}
									min={
										start.date && end.date === start.date
											? start.time
											: undefined
									}
									onChange={(value) =>
										setMeta({ _event_end: combineDateTime(end.date, value) })
									}
								/>
							</FlexItem>
						) : null}
					</Flex>
				</div>
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
