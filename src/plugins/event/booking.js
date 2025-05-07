import { select } from '@wordpress/data';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import icons from './icons';

import './booking.scss';

import { useEntityProp } from '@wordpress/core-data';
import BookingForms from './booking/BookingForms';
import BookingSpaces from './booking/BookingSpaces';
import EnableBooking from './booking/EnableBooking';
import PriceAdjustments from './booking/PriceAdjustments';

const BookingSidebar = () => {
	const postType = select( 'core/editor' ).getCurrentPostType();
	const currentPost = select( 'core/editor' ).getCurrentPost();
	const postId = currentPost.id;
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	if ( postType !== 'event' ) return <></>;

	return (
		<>
			<PluginSidebarMoreMenuItem target="event-booking-sidebar" icon={ icons.ticket }>
				{ __( 'Booking' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar name="event-booking-sidebar" icon={ icons.ticket } title="Booking options">
				<EnableBooking meta={ meta } setMeta={ setMeta } />
				<BookingSpaces meta={ meta } setMeta={ setMeta } postId={ postId } postType={ postType } />
				<BookingForms meta={ meta } setMeta={ setMeta } postId={ postId } postType={ postType } />
				<PriceAdjustments meta={ meta } setMeta={ setMeta } postId={ postId } postType={ postType } />
				<MailSettings meta={ meta } setMeta={ setMeta } postId={ postId } postType={ postType } />
			</PluginSidebar>
		</>
	);
};

export default BookingSidebar;
