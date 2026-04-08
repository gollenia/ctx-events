import PanelTitle from '@events/adminfields/PanelTitle';
import {
	Button,
	Flex,
	PanelBody,
	__experimentalSpacer as Spacer,
	TextControl,
} from '@wordpress/components';

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import icons from '../icons';
import TicketModal from './TicketModal';
import { type BookingSidebarProps, isBookingEnabled } from './types';

const BookingSpaces = ({
	meta,
	updateMeta,
	postId,
	postType,
}: BookingSidebarProps) => {
	const enabled = isBookingEnabled(meta);
	const [showTickets, setShowTickets] = useState(false);

	return (
		<PanelBody
			title={
				<PanelTitle icon={icons.people} title={__('Attendees', 'ctx-events')} />
			}
			initialOpen={true}
		>
			<Flex gap={4} direction="column">
				<TextControl
					label={__('Spaces overall', 'ctx-events')}
					value={String(meta._booking_capacity ?? '')}
					type="number"
					onChange={(value) => {
						updateMeta({ _booking_capacity: value });
					}}
					disabled={!enabled}
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			</Flex>
			<Spacer marginBottom={4} />
			<Button
				onClick={() => setShowTickets(true)}
				variant="secondary"
				disabled={!enabled}
			>
				{__('Edit Tickets', 'ctx-events')}
			</Button>

			<TicketModal
				meta={meta}
				updateMeta={updateMeta}
				postId={postId}
				postType={postType}
				showTickets={showTickets}
				setShowTickets={setShowTickets}
			/>
		</PanelBody>
	);
};

export default BookingSpaces;
