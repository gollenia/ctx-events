import { Button, __experimentalHStack as HStack } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { dispatch, select, useDispatch } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';


const BookingStatus = () => {
	const postType = select('core/editor').getCurrentPostType();
	const currentPost = select('core/editor').getCurrentPost();
	const postId = currentPost.id;
	const { openGeneralSidebar } = useDispatch('core/edit-post');

	const [meta, setMeta] = useEntityProp('postType', postType, 'meta');

	if (postType !== 'ctx-event') return null;

	const openBookingSidebar = () => {
		dispatch('core/edit-post').openGeneralSidebar('event-booking-sidebar');
	};

	const isEnabled = meta._event_rsvp;

	return (
		<PluginPostStatusInfo>
			<HStack className="editor-post-panel__row">
				<div className="editor-post-panel__row-label">
					{__('Bookings', 'ctx-events')}
				</div>
				<div className="editor-post-panel__row-value">
					<Button onClick={openBookingSidebar} variant="tertiary" isCompact>
						{isEnabled ? __('enabled', 'ctx-events') : __('disabled', 'ctx-events')}
					</Button>
				</div>
			</HStack>
		</PluginPostStatusInfo>
	);
};
export default BookingStatus;
