import { CheckboxControl, TextControl, Flex, FlexItem } from '@wordpress/components';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

const datetimeSelector = () => {
    const postType = useSelect((select) => select('core/editor').getCurrentPostType(), []);
    const { lockPostSaving, unlockPostSaving } = useDispatch('core/editor');
    const { createNotice, removeNotice } = useDispatch('core/notices');

    const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
	
	const { isSaving, isPublishing, isPublishSidebarOpened } = useSelect((select) => {
        const editor = select('core/editor');

        return {
            isSaving: editor.isSavingPost(),
            isPublishing: editor.isPublishingPost(),
			isPublishSidebarOpened: editor.isPublishSidebarOpened(),
        };
    }, []);

	const hasAttemptedSave = useRef(false);

    if (postType !== 'ctx-event') return null;

    const splitDateTime = (dtString) => {
        if (!dtString) return { date: '', time: '' };
        const [date, time] = dtString.split('T');
        return { date, time: time ? time.slice(0, 5) : '00:00' };
    };

    const combineDateTime = (date, time) => {
        if (!date) return '';
        return `${date}T${time || '00:00'}`;
    };

	const addOneHour = (isoString) => {
		if (!isoString) return '';
		const date = new Date(isoString);
		date.setHours(date.getHours() + 1);
		const offset = date.getTimezoneOffset() * 60000;
		return new Date(date - offset).toISOString().slice(0, 16);
	};

    useEffect(() => {
        if (isSaving || isPublishing) {
            hasAttemptedSave.current = true;
        }

        const noticeId = 'event_date_notice';

		const isMissingStart = !meta._event_start;
    	const isInvalidRange = meta._event_start && meta._event_end && meta._event_start > meta._event_end;

		if(!isMissingStart && !isInvalidRange) {
			unlockPostSaving('event_date_lock');
			removeNotice(noticeId);
			hasAttemptedSave.current = false;
			return;
		} 
        
		lockPostSaving('event_date_lock');
		
		if (hasAttemptedSave.current || isPublishSidebarOpened) {
			const message = isMissingStart 
				? __('please set a start date before saving.', 'ctx-events') 
				: __('The end must be after the start!', 'ctx-events');

			createNotice('error', message, {
				id: noticeId,
				isDismissible: true,
			});
		}
		
    }, [meta._event_start, isSaving, isPublishing, isPublishSidebarOpened]);

    const start = splitDateTime(meta._event_start);
    const end = splitDateTime(meta._event_end);
    const isAllDay = !!parseInt(meta._event_all_day);

    return (
        <PluginDocumentSettingPanel
            name="events-datetime-settings"
            title={__('Date and Time', 'ctx-events')}
        >
            <p><strong>{__('Start', 'ctx-events')}</strong></p>
            <Flex>
                <FlexItem isBlock>
                    <TextControl
                        type="date"
                        value={start.date}
                        onChange={(value) => {
							const newStart = combineDateTime(value, start.time);
							const updates = { _event_start: newStart };
							if (!meta._event_end || newStart >= meta._event_end) {
								updates._event_end = addOneHour(newStart);
							}
							setMeta(updates);
						}}
                    />
                </FlexItem>
                {!isAllDay && (
                    <FlexItem>
                        <TextControl
                            type="time"
                            value={start.time}
							min={start.date}
                            onChange={(value) => {
								const newStart = combineDateTime(start.date, value);
                    			const updates = { _event_start: newStart };
								if (!meta._event_end || newStart >= meta._event_end) {
									updates._event_end = addOneHour(newStart);
								}
								setMeta(updates);
							}}
                        />
                    </FlexItem>
                )}
            </Flex>

            <p style={{ marginTop: '10px' }}><strong>{__('End', 'ctx-events')}</strong></p>
            <Flex>
                <FlexItem isBlock>
                    <TextControl
                        type="date"
                        value={end.date}
						min={start.date}
                        onChange={(value) => setMeta({ _event_end: combineDateTime(value, end.time) })}
                    />
                </FlexItem>
                {!isAllDay && (
                    <FlexItem>
                        <TextControl
                            type="time"
                            value={end.time}
							min={
								start.date && end.date === start.date ? start.time : undefined
							}
                            onChange={(value) => setMeta({ _event_end: combineDateTime(end.date, value) })}
                        />
                    </FlexItem>
                )}
            </Flex>

            <CheckboxControl
                label={__('All Day', 'ctx-events')}
                checked={isAllDay}
                onChange={(val) => setMeta({ _event_all_day: val ? 1 : 0 })}
            />
        </PluginDocumentSettingPanel>
    );
};

export default datetimeSelector;