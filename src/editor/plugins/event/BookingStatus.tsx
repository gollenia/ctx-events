import { Button, __experimentalHStack as HStack } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { dispatch, useSelect } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

import type { EditorSelection, EventMeta } from './types';

const BookingStatus = () => {
	const postType = useSelect((select) => {
		const editor = select('core/editor') as EditorSelection;
		return editor.getCurrentPostType() ?? '';
	}, []);

	const [rawMeta] = useEntityProp('postType', postType, 'meta');
	const meta = (rawMeta ?? {}) as EventMeta;

	if (postType !== 'ctx-event') {
		return null;
	}

	const openBookingSidebar = () => {
		dispatch('core/edit-post').openGeneralSidebar('event-booking-sidebar');
	};

	return (
		<PluginPostStatusInfo>
			<HStack className="editor-post-panel__row">
				<div className="editor-post-panel__row-label">
					{__('Bookings', 'ctx-events')}
				</div>
				<div className="editor-post-panel__row-value">
					<Button onClick={openBookingSidebar} variant="tertiary" isCompact>
						{meta._booking_enabled
							? __('enabled', 'ctx-events')
							: __('disabled', 'ctx-events')}
					</Button>
				</div>
			</HStack>
		</PluginPostStatusInfo>
	);
};

export default BookingStatus;
