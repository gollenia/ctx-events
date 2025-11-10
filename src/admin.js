import './admin.scss';
import { BookingsAdmin } from './admin/bookings/index.js';
import { GatewayAdmin } from './admin/gateways/index.js';
import './jquery-ui.min.scss';

const bookingEditRoot = document.getElementById( 'booking-edit' );

BookingsAdmin();
GatewayAdmin();
