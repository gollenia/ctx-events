import { select } from '@wordpress/data';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import icons from './icons';

import './BookingSidebar/style.scss';

import { useEntityProp } from '@wordpress/core-data';
import BookingForms from './BookingSidebar/BookingForms';
import BookingSpaces from './BookingSidebar/BookingSpaces';
import EnableBooking from './BookingSidebar/EnableBooking';
import MailSettings from './BookingSidebar/MailSettings';
import PriceAdjustments from './BookingSidebar/PriceOptions';

const BookingSidebar = () => {
	const postType = select('core/editor').getCurrentPostType();
	const currentPost = select('core/editor').getCurrentPost();
	const postId = currentPost.id;
	const [meta, setMeta] = useEntityProp('postType', postType, 'meta');

	if (postType !== 'ctx-event') return null;
	
	return (
		<>
			<PluginSidebarMoreMenuItem
				target="event-booking-sidebar"
				icon={icons.ticket}
			>
				{__('Booking')}
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name="event-booking-sidebar"
				icon={icons.ticket}
				title="Booking options"
			>
				<EnableBooking meta={meta} setMeta={setMeta} />
				<BookingSpaces
					meta={meta}
					setMeta={setMeta}
					postId={postId}
					postType={postType}
				/>
				<BookingForms
					meta={meta}
					setMeta={setMeta}
					postId={postId}
					postType={postType}
				/>
				<PriceAdjustments
					meta={meta}
					setMeta={setMeta}
					postId={postId}
					postType={postType}
				/>
				<MailSettings
					meta={meta}
					setMeta={setMeta}
					postId={postId}
					postType={postType}
				/>
			</PluginSidebar>
		</>
	);
};

export default BookingSidebar;
