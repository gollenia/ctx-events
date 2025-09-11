import ReactDOM from 'react-dom';
import './style.scss';

import BookingTable from './BookingTable';

function BookingsAdmin() {
	document.addEventListener( 'DOMContentLoaded', () => {
		const rootElement = document.getElementById( 'em-bookings-admin' );
		if ( ! rootElement ) return;

		const app = ReactDOM.createRoot( rootElement );

		app.render( <BookingTable /> );
	} );
}
export { BookingsAdmin };
