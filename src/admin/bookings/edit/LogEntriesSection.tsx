import { Flex, FlexItem } from '@contexis/wp-react-form';
import { formatDate } from '@events/i18n/datetime';
import { Panel, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	caution,
	Icon,
	keyboardReturn,
	notAllowed,
	plusCircle,
	scheduled,
	thumbsDown,
	thumbsUp,
	update,
} from '@wordpress/icons';
import type {
	BookingDetail,
	BookingLogEntryResource,
} from '../../../types/types';

type Props = {
	booking: BookingDetail;
};

const EVENT_LABELS: Record<BookingLogEntryResource['eventType'], string> = {
	created: __('Created', 'ctx-events'),
	updated: __('Updated', 'ctx-events'),
	deleted: __('Deleted', 'ctx-events'),
	approved: __('Approved', 'ctx-events'),
	rejected: __('Rejected', 'ctx-events'),
	cancelled: __('Cancelled', 'ctx-events'),
	attendee_cancelled: __('Attendee cancelled', 'ctx-events'),
	restored: __('Restored', 'ctx-events'),
	email_warning: __('Email warning', 'ctx-events'),
};

const levelClassMap: Record<BookingLogEntryResource['level'], string> = {
	info: 'info',
	warning: 'warning',
	error: 'error',
};

const LogIcon = ({
	eventType,
}: {
	eventType: BookingLogEntryResource['eventType'];
}) => {
	const icon = (() => {
		switch (eventType) {
			case 'created':
				return plusCircle;
			case 'updated':
				return update;
			case 'deleted':
				return notAllowed;
			case 'approved':
				return thumbsUp;
			case 'rejected':
				return thumbsDown;
			case 'restored':
				return keyboardReturn;
			case 'attendee_cancelled':
				return notAllowed;
			case 'email_warning':
				return scheduled;
			default:
				return caution;
		}
	})();

	return <Icon icon={icon} />;
};

const LogEntriesSection = ({ booking }: Props) => {
	const logEntries = [...booking.logEntries].reverse();

	return (
		<Panel header={__('Activity', 'ctx-events')}>
			<PanelBody>
				{logEntries.length === 0 ? (
					<p className="booking-edit__empty">
						{__('No activity yet.', 'ctx-events')}
					</p>
				) : (
					<Flex direction="column" as="ul" gap="0.75rem">
						{logEntries.map((entry, index) => (
							<Flex
								key={`${entry.timestamp}-${entry.eventType}-${index}`}
								data-level={entry.level}
							>
								<Flex align="flex-start" gap="1rem">
									<FlexItem
										className={`booking-edit__activity-icon booking-edit__activity-icon--${levelClassMap[entry.level]}`}
										style={{ flex: '0 0 auto' }}
									>
										<LogIcon eventType={entry.eventType} />
									</FlexItem>
									<Flex direction="column" style={{ flex: 1 }}>
										<Flex
											justify="space-between"
											align="center"
											className="booking-edit__activity-header"
										>
											<Flex align="center" gap="1rem">
												<strong>
													{EVENT_LABELS[entry.eventType] ?? entry.eventType}
												</strong>
												<span className="">
													{entry.actorName || __('Guest', 'ctx-events')}
												</span>
											</Flex>
											<span>{formatDate(entry.timestamp)}</span>
										</Flex>
										{entry.message ? (
											<span className="booking-edit__activity-actor">
												{entry.message}
											</span>
										) : null}
									</Flex>
								</Flex>
							</Flex>
						))}
					</Flex>
				)}
			</PanelBody>
		</Panel>
	);
};

export default LogEntriesSection;
