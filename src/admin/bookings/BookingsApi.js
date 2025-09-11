import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * @typedef {Object} BookingsResponse
 * @property {Array} items
 * @property {number} total
 */

/**
 * @param {Object} query
 * @param {AbortSignal} [signal]
 * @returns {Promise<BookingsResponse>}
 */
export async function getBookings( query, signal ) {
	const path = addQueryArgs( '/events/v2/bookings', query );

	// parse:false, um Headers lesen zu können
	const res = await apiFetch( { path, method: 'GET', parse: false, signal } );
	const body = await res.json();

	const items = Array.isArray( body ) ? body : body.items ?? [];
	const totalHeader = Number( res.headers.get( 'X-WP-Total' ) );
	const total = Number.isFinite( totalHeader ) ? totalHeader : Number( body.total ?? items.length );

	return { items, total };
}
