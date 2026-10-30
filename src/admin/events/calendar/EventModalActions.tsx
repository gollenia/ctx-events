import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { CalendarEvent as ScheduleEvent } from '@schedule-x/calendar';
import type { Event } from '../../../types/types';
import { getLocale } from '@events/i18n';
import { actions } from '../actions';
import EventCancelConfirmModal from '../EventCancelConfirmModal';
import EventDuplicateModal from '../EventDuplicateModal';

interface EventModalActionsProps {
	event: ScheduleEvent;
	onCancelled: (eventId: string | number) => void;
}

const EventModalActions = ({ event, onCancelled }: EventModalActionsProps) => {
	const [isCancelOpen, setIsCancelOpen] = useState(false);
	const [isDuplicateOpen, setIsDuplicateOpen] = useState(false);
	const cancelAction = actions.find((action) => action.id === 'cancel');
	const duplicateAction = actions.find((action) => action.id === 'duplicate');
	const eventId = Number(event.id);
	const recurrenceSeriesId = (
		event as ScheduleEvent & { recurrenceSeriesId?: number }
	).recurrenceSeriesId;
	const start = event.start as Temporal.ZonedDateTime;
	const end = event.end as Temporal.ZonedDateTime;
	const calendarEvent = { id: eventId, name: event.title ?? '' } as Event;

	if (!cancelAction || (!recurrenceSeriesId && !Number.isInteger(eventId))) return null;

	return (
		<>
			<div className="ctx-events-calendar__modal-details">
				<strong className="ctx-events-calendar__modal-title">{event.title || __('(No title)', 'ctx-events')}</strong>
				<span>
					{start.toLocaleString(getLocale(), { dateStyle: 'medium', timeStyle: 'short' })}
					{' – '}
					{end.toLocaleString(getLocale(), { dateStyle: 'medium', timeStyle: 'short' })}
				</span>
				{event.people && event.people.length > 0 && <span>{event.people.join(', ')}</span>}
				{event.location && <span>{event.location}</span>}
				{event.description && <span>{event.description}</span>}
			</div>
			<div className="ctx-events-calendar__modal-actions">
				{recurrenceSeriesId ? (
					<>
						<a className="button button-secondary" href={`/wp-admin/post.php?post=${recurrenceSeriesId}&action=edit`}>{__('Edit recurrence', 'ctx-events')}</a>
						<button type="button" className="button button-secondary" onClick={() => {
							void apiFetch({ path: `/events/v3/events/${recurrenceSeriesId}/detach-occurrence`, method: 'POST', data: { occurrence_key: event.id } }).then(() => onCancelled(event.id));
						}}>{__('Detach occurrence', 'ctx-events')}</button>
					</>
				) : (
					<>
						<a className="button button-secondary" href={`/wp-admin/post.php?post=${eventId}&action=edit`}>{__('Edit event', 'ctx-events')}</a>
						{duplicateAction && <button type="button" className="button button-secondary" onClick={() => setIsDuplicateOpen(true)}>{__('Duplicate', 'ctx-events')}</button>}
						<button type="button" className="button-link-delete" onClick={() => setIsCancelOpen(true)}>{__('Cancel event', 'ctx-events')}</button>
					</>
				)}
			</div>
			{isCancelOpen && <EventCancelConfirmModal action={cancelAction} item={calendarEvent} onClose={() => setIsCancelOpen(false)} onActionPerformed={() => onCancelled(eventId)} />}
			{isDuplicateOpen && duplicateAction && <EventDuplicateModal action={duplicateAction} item={calendarEvent} onClose={() => setIsDuplicateOpen(false)} />}
		</>
	);
};

export default EventModalActions;
