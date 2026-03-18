import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

import BookingForms from './BookingSidebar/BookingForms';
import BookingSpaces from './BookingSidebar/BookingSpaces';
import EnableBooking from './BookingSidebar/EnableBooking';
import MailSettings from './BookingSidebar/MailSettings';
import PriceAdjustments from './BookingSidebar/PriceOptions';
import type { BookingMeta } from './BookingSidebar/types';
import icons from './icons';

import './BookingSidebar/style.scss';

const BookingSidebar = () => {
	const { postId, postType } = useSelect((select) => {
		const editor = select('core/editor') as {
			getCurrentPost: () => { id?: number } | null;
			getCurrentPostType: () => string | null;
		};
		const currentPost = editor.getCurrentPost();

		return {
			postId: currentPost?.id ?? 0,
			postType: editor.getCurrentPostType() ?? '',
		};
	}, []);

	const [rawMeta, setMeta] = useEntityProp('postType', postType, 'meta');
	const meta = (rawMeta ?? {}) as BookingMeta;

	if (postType !== 'ctx-event' || !postId) {
		return null;
	}

	const updateMeta = (updates: Partial<BookingMeta>) => {
		setMeta({
			...meta,
			...updates,
		});
	};

	return (
		<>
			<PluginSidebarMoreMenuItem
				target="event-booking-sidebar"
				icon={icons.ticket}
			>
				{__('Booking', 'ctx-events')}
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name="event-booking-sidebar"
				icon={icons.ticket}
				title={__('Booking options', 'ctx-events')}
			>
				<EnableBooking
					meta={meta}
					updateMeta={updateMeta}
					postId={postId}
					postType={postType}
				/>
				<BookingSpaces
					meta={meta}
					updateMeta={updateMeta}
					postId={postId}
					postType={postType}
				/>
				<BookingForms
					meta={meta}
					updateMeta={updateMeta}
					postId={postId}
					postType={postType}
				/>
				<PriceAdjustments
					meta={meta}
					updateMeta={updateMeta}
					postId={postId}
					postType={postType}
				/>
				<MailSettings
					meta={meta}
					updateMeta={updateMeta}
					postId={postId}
					postType={postType}
				/>
			</PluginSidebar>
		</>
	);
};

export default BookingSidebar;
