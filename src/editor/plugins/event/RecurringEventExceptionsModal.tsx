import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Flex,
	FlexItem,
	Modal,
	Notice,
	Spinner,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import SmallDataTable, {
	type SmallDataTableField,
} from '../../../shared/small-data-table';
import StatusPill from '../../../shared/status-pill';

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

type DetachedEventRecord = {
	id: number;
	title?: { raw?: string; rendered?: string };
};

type OccurrenceRow = {
	id: string;
	occurrence?: Occurrence;
	exception?: RecurrenceException;
	startsAt: string;
};

type Props = {
	postId: number;
	recurringEventTitle: string;
	exceptions: Record<string, RecurrenceException>;
	show: boolean;
	onClose: () => void;
	onChange: (exceptions: Record<string, RecurrenceException>) => void;
};

const formatDate = (value: string): string =>
	new Intl.DateTimeFormat(undefined, {
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
		const byDate =
			new Date(left.startsAt).getTime() - new Date(right.startsAt).getTime();
		return Number.isNaN(byDate) ? left.id.localeCompare(right.id) : byDate;
	});
};

const RecurringEventExceptionsModal = ({
	postId,
	recurringEventTitle,
	exceptions,
	show,
	onClose,
	onChange,
}: Props) => {
	const [occurrences, setOccurrences] = useState<Occurrence[]>([]);
	const [isLoading, setIsLoading] = useState(false);
	const [detachingOccurrenceId, setDetachingOccurrenceId] = useState<
		string | null
	>(null);
	const [error, setError] = useState<string | null>(null);
	const [detachedEventTitles, setDetachedEventTitles] = useState<
		Record<number, string>
	>({});
	const detachedEventIds = [
		...new Set(
			Object.values(exceptions)
				.filter(
					(
						exception,
					): exception is RecurrenceException & { eventPostId: number } =>
						exception.type === 'detached' && Boolean(exception.eventPostId),
				)
				.map((exception) => exception.eventPostId),
		),
	];
	const missingDetachedEventIds = detachedEventIds.filter(
		(eventId) => detachedEventTitles[eventId] === undefined,
	);
	const missingDetachedEventIdsKey = missingDetachedEventIds.join(',');

	useEffect(() => {
		if (!show || missingDetachedEventIds.length === 0) return;

		let isCurrent = true;
		const loadDetachedEvents = async (): Promise<void> => {
			try {
				const events = await apiFetch<DetachedEventRecord[]>({
					path: `/wp/v2/ctx-event?include=${missingDetachedEventIds.join(',')}&per_page=${missingDetachedEventIds.length}&context=edit&_fields=id,title`,
				});
				if (!isCurrent) return;
				setDetachedEventTitles((current) => ({
					...current,
					...Object.fromEntries(
						events.map((event) => [
							event.id,
							event.title?.raw ?? event.title?.rendered ?? '',
						]),
					),
				}));
			} catch {
				// Keep the generic occurrence label if a detached event is unavailable.
			}
		};

		void loadDetachedEvents();
		return () => {
			isCurrent = false;
		};
	}, [missingDetachedEventIdsKey, show]);

	useEffect(() => {
		if (!show) return;

		const load = async (): Promise<void> => {
			setIsLoading(true);
			setError(null);
			try {
				const response = await apiFetch<Occurrence[]>({
					path: `/events/v3/events?with_recurrences=true&recurring_event=${postId}&scope=1-year&per_page=366&order=asc`,
				});
				setOccurrences(
					response.filter((occurrence) => occurrence.type === 'virtual'),
				);
			} catch (requestError) {
				setError(
					requestError instanceof Error
						? requestError.message
						: __('Could not load occurrences.', 'ctx-events'),
				);
			} finally {
				setIsLoading(false);
			}
		};

		void load();
	}, [postId, show]);

	if (!show) return null;

	const cancel = (occurrenceId: string): void =>
		onChange({
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
			onChange({
				...exceptions,
				[occurrenceId]: { type: 'detached', eventPostId: response.eventId },
			});
		} catch (requestError) {
			setError(
				requestError instanceof Error
					? requestError.message
					: __('Could not detach occurrence.', 'ctx-events'),
			);
		} finally {
			setDetachingOccurrenceId(null);
		}
	};
	const rows = occurrenceRows(occurrences, exceptions);
	const renderActions = (row: OccurrenceRow) => {
		const { exception, occurrence } = row;

		if (exception?.type === 'detached' && exception.eventPostId) {
			return (
				<div className="row-actions">
					<a
						className="view"
						href={`/wp-admin/post.php?post=${exception.eventPostId}&action=edit`}
					>
						{__('Edit detached event', 'ctx-events')}
					</a>
					{' | '}
					<Button
						variant="link"
						className="trash"
						onClick={() => restore(row.id)}
					>
						{__('Remove exception', 'ctx-events')}
					</Button>
				</div>
			);
		}

		if (exception) {
			return (
				<div className="row-actions">
					<Button
						variant="link"
						className="trash"
						onClick={() => restore(row.id)}
					>
						{__('Remove exception', 'ctx-events')}
					</Button>
				</div>
			);
		}

		if (!occurrence) return null;

		return (
			<div className="row-actions">
				<Button
					variant="link"
					className="trash"
					isDestructive
					onClick={() => cancel(row.id)}
				>
					{__('Cancel occurrence', 'ctx-events')}
				</Button>
				{' | '}
				<Button
					variant="link"
					className="view"
					isBusy={detachingOccurrenceId === row.id}
					disabled={detachingOccurrenceId !== null}
					onClick={() => void detach(row.id)}
				>
					{__('Detach occurrence', 'ctx-events')}
				</Button>
			</div>
		);
	};
	const fields: SmallDataTableField<OccurrenceRow>[] = [
		{
			id: 'occurrence',
			label: __('Occurrence', 'ctx-events'),
			cellClassName: (row) =>
				row.exception?.type === 'cancelled'
					? 'ctx-recurring-event-exception__title--cancelled'
					: undefined,
			render: (row) => {
				return (
					<>
						<strong>
							{row.exception?.type === 'cancelled'
								? recurringEventTitle || __('Occurrence', 'ctx-events')
								: row.exception?.type === 'detached' &&
										row.exception.eventPostId
									? detachedEventTitles[row.exception.eventPostId] ||
										__('Occurrence', 'ctx-events')
									: (row.occurrence?.name ?? __('Occurrence', 'ctx-events'))}
						</strong>
						{renderActions(row)}
					</>
				);
			},
		},
		{
			id: 'date',
			label: __('Date', 'ctx-events'),
			render: (row) =>
				row.occurrence
					? formatDate(row.occurrence.startDate)
					: formatOccurrenceKey(row.id),
		},
		{
			id: 'status',
			label: __('Status', 'ctx-events'),
			render: (row) => {
				if (row.exception?.type === 'detached') {
					return (
						<StatusPill state="info">{__('Detached', 'ctx-events')}</StatusPill>
					);
				}

				if (row.exception?.type === 'cancelled') {
					return (
						<StatusPill state="error">
							{__('Cancelled', 'ctx-events')}
						</StatusPill>
					);
				}

				return (
					<StatusPill state="success">
						{__('Scheduled', 'ctx-events')}
					</StatusPill>
				);
			},
		},
	];

	return (
		<Modal
			title={__('Manage exceptions', 'ctx-events')}
			onRequestClose={onClose}
			size="large"
			className="ctx-recurring-event-exceptions-modal"
		>
			<p>
				{__(
					'Cancelled occurrences are saved with this recurring event when you update it.',
					'ctx-events',
				)}
			</p>
			{error && (
				<Notice status="error" isDismissible={false}>
					{error}
				</Notice>
			)}
			{isLoading ? (
				<Spinner />
			) : (
				<>
					<SmallDataTable
						items={rows}
						fields={fields}
						getRowKey={(row) => row.id}
						emptyMessage={__(
							'No occurrences in the next twelve months.',
							'ctx-events',
						)}
					/>
					<Flex justify="flex-end" style={{ marginTop: '1rem' }}>
						<FlexItem>
							<Button variant="secondary" onClick={onClose}>
								{__('Done', 'ctx-events')}
							</Button>
						</FlexItem>
					</Flex>
				</>
			)}
		</Modal>
	);
};

export default RecurringEventExceptionsModal;
