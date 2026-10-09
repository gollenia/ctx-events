import { Button, Flex, FlexItem, Modal, Notice, Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export type RecurrenceException = {
	type: 'cancelled' | 'detached';
	eventPostId?: number;
};

type Occurrence = {
	id: string;
	type: 'virtual';
	name: string;
	startDate: string;
	endDate: string;
};

type DetachResponse = { eventId: number };

type OccurrenceRow = {
	id: string;
	occurrence?: Occurrence;
	exception?: RecurrenceException;
	startsAt: string;
};

type Props = {
	postId: number;
	exceptions: Record<string, RecurrenceException>;
	show: boolean;
	onClose: () => void;
	onChange: (exceptions: Record<string, RecurrenceException>) => void;
};

const formatDate = (value: string): string => new Intl.DateTimeFormat(undefined, {
	dateStyle: 'medium',
	timeStyle: 'short',
}).format(new Date(value));

const formatOccurrenceKey = (occurrenceKey: string): string => {
	const match = /^recurrence:\d+:(.+)$/.exec(occurrenceKey);
	return match ? formatDate(match[1]) : occurrenceKey;
};

const startsAtFromOccurrenceKey = (occurrenceKey: string): string => {
	const match = /^recurrence:\d+:(.+)$/.exec(occurrenceKey);
	return match?.[1] ?? occurrenceKey;
};

const occurrenceRows = (
	occurrences: Occurrence[],
	exceptions: Record<string, RecurrenceException>,
): OccurrenceRow[] => {
	const rows = new Map<string, OccurrenceRow>();

	for (const occurrence of occurrences) {
		rows.set(occurrence.id, {
			id: occurrence.id,
			occurrence,
			exception: exceptions[occurrence.id],
			startsAt: occurrence.startDate,
		});
	}

	for (const [id, exception] of Object.entries(exceptions)) {
		if (rows.has(id)) continue;
		rows.set(id, { id, exception, startsAt: startsAtFromOccurrenceKey(id) });
	}

	return [...rows.values()].sort((left, right) => {
		const byDate = new Date(left.startsAt).getTime() - new Date(right.startsAt).getTime();
		return Number.isNaN(byDate) ? left.id.localeCompare(right.id) : byDate;
	});
};

const RecurringEventExceptionsModal = ({ postId, exceptions, show, onClose, onChange }: Props) => {
	const [occurrences, setOccurrences] = useState<Occurrence[]>([]);
	const [isLoading, setIsLoading] = useState(false);
	const [detachingOccurrenceId, setDetachingOccurrenceId] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);

	useEffect(() => {
		if (!show) return;

		const load = async (): Promise<void> => {
			setIsLoading(true);
			setError(null);
			try {
				const response = await apiFetch<Occurrence[]>({
					path: `/events/v3/events?with_recurrences=true&recurring_event=${postId}&scope=1-year&per_page=366&order=asc`,
				});
				setOccurrences(response.filter((occurrence) => occurrence.type === 'virtual'));
			} catch (requestError) {
				setError(requestError instanceof Error ? requestError.message : __('Could not load occurrences.', 'ctx-events'));
			} finally {
				setIsLoading(false);
			}
		};

		void load();
	}, [postId, show]);

	if (!show) return null;

	const cancel = (occurrenceId: string): void => onChange({
		...exceptions,
		[occurrenceId]: { type: 'cancelled' },
	});
	const restore = (occurrenceId: string): void => {
		const { [occurrenceId]: _removed, ...remaining } = exceptions;
		onChange(remaining);
	};
	const detach = async (occurrenceId: string): Promise<void> => {
		setDetachingOccurrenceId(occurrenceId);
		setError(null);
		try {
			const response = await apiFetch<DetachResponse>({
				path: `/events/v3/events/${postId}/detach-occurrence`,
				method: 'POST',
				data: { occurrence_key: occurrenceId },
			});
			onChange({ ...exceptions, [occurrenceId]: { type: 'detached', eventPostId: response.eventId } });
		} catch (requestError) {
			setError(requestError instanceof Error ? requestError.message : __('Could not detach occurrence.', 'ctx-events'));
		} finally {
			setDetachingOccurrenceId(null);
		}
	};
	const rows = occurrenceRows(occurrences, exceptions);

	return <Modal title={__('Manage exceptions', 'ctx-events')} onRequestClose={onClose} size="large" className="ctx-recurring-event-exceptions-modal">
		<p>{__('Cancelled occurrences are saved with this recurring event when you update it.', 'ctx-events')}</p>
		{error && <Notice status="error" isDismissible={false}>{error}</Notice>}
		{isLoading ? <Spinner /> : <>
			<table className="widefat striped">
				<thead><tr><th>{__('Occurrence', 'ctx-events')}</th><th>{__('Status', 'ctx-events')}</th><th className="column-actions">{__('Action', 'ctx-events')}</th></tr></thead>
				<tbody>
					{rows.map((row) => {
						const { exception, occurrence } = row;
						return <tr key={row.id}>
							<td>{occurrence ? <><strong>{occurrence.name}</strong><br />{formatDate(occurrence.startDate)}</> : formatOccurrenceKey(row.id)}</td>
							<td>{exception ? (exception.type === 'detached' ? __('Detached', 'ctx-events') : __('Cancelled', 'ctx-events')) : __('Scheduled', 'ctx-events')}</td>
							<td>{exception ? (exception.type === 'detached' && exception.eventPostId ? <a className="button button-secondary" href={`/wp-admin/post.php?post=${exception.eventPostId}&action=edit`}>{__('Edit detached event', 'ctx-events')}</a> : <Button variant="tertiary" onClick={() => restore(row.id)}>{__('Remove exception', 'ctx-events')}</Button>) : occurrence ? <Flex gap="8px" justify="flex-end"><FlexItem><Button variant="tertiary" isDestructive onClick={() => cancel(row.id)}>{__('Cancel occurrence', 'ctx-events')}</Button></FlexItem><FlexItem><Button variant="secondary" isBusy={detachingOccurrenceId === row.id} disabled={detachingOccurrenceId !== null} onClick={() => void detach(row.id)}>{__('Detach occurrence', 'ctx-events')}</Button></FlexItem></Flex> : null}</td>
						</tr>;
					})}
					{rows.length === 0 && <tr><td colSpan={3}>{__('No occurrences in the next twelve months.', 'ctx-events')}</td></tr>}
				</tbody>
			</table>
			<Flex justify="flex-end" style={{ marginTop: '1rem' }}><FlexItem><Button variant="secondary" onClick={onClose}>{__('Done', 'ctx-events')}</Button></FlexItem></Flex>
		</>}
	</Modal>;
};

export default RecurringEventExceptionsModal;
