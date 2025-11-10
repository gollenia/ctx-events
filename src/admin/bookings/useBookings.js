import { useEffect, useMemo, useRef, useState } from 'react';
import { getBookings } from './BookingsApi.js';
import { viewToQuery } from './filters.js';

/**
 * @returns {{ items:any[], total:number, loading:boolean, error:null|string }}
 */
export function useBookings( view ) {
	const [ state, setState ] = useState( { items: [], total: 0, loading: true, error: null } );
	const abortRef = useRef( null );

	// Query deterministisch ableiten
	const query = useMemo(
		() => viewToQuery( view ),
		[
			view.search,
			view.page,
			view.perPage,
			view.sort?.orderby,
			view.sort?.order,
			JSON.stringify( view.filters ), // ok hier, weil Filter ein Array of POJOs ist
		]
	);

	useEffect( () => {
		abortRef.current?.abort?.();
		const controller = new AbortController();
		abortRef.current = controller;

		setState( ( s ) => ( { ...s, loading: true, error: null } ) );

		getBookings( query, controller.signal )
			.then( ( { items, total } ) => {
				const normalized = items.map( ( b ) => ( {
					...b,
					date: b.date?.includes?.( 'T' )
						? b.date
						: new Date( b.date?.replace?.( ' ', 'T' ) || b.date ).toISOString(),
				} ) );
				setState( { items: normalized, total, loading: false, error: null } );
			} )
			.catch( ( e ) => {
				if ( e?.name !== 'AbortError' ) {
					setState( ( s ) => ( { ...s, loading: false, error: e?.message || String( e ) } ) );
				}
			} );

		return () => controller.abort();
	}, [ query ] );

	return state;
}
