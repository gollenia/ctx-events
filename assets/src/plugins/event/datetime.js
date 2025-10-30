/**
 * Adds a metabox for the page color settings
 */

/**
 * WordPress dependencies
 */
import { CheckboxControl, TextControl } from '@wordpress/components';
import { dispatch, select, useDispatch } from '@wordpress/data';

import { PluginDocumentSettingPanel } from '@wordpress/editor';
import './datetime.scss';

import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

const datetimeSelector = () => {
	const postType = select( 'core/editor' ).getCurrentPostType();
	const { lockPostSaving, unlockPostSaving } = useDispatch( 'core/editor' );

	if ( postType !== 'event' ) return <></>;

	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	if ( ! meta._event_start || ! meta._event_end ) {
		wp.data.dispatch( 'core/notices' ).createNotice( 'warning', 'Do not forget about a date your post!', {
			id: 'rudr-featured-img',
			isDismissible: false,
		} );
		dispatch( 'core/editor' ).lockPostSaving( 'requiredValueLock' );
	} else {
		dispatch( 'core/notices' ).removeNotice( 'rudr-featured-img' );
		unlockPostSaving( 'requiredValueLock' );
	}

	const compareTime = ( start, end ) => {
		const startDate = new Date( '01/01/1970 ' + start );
		const endDate = new Date( '01/01/1970 ' + end );
		return startDate < endDate;
	};

	const getNow = ( hourOffset = 0 ) => {
		let endDate = new Date();
		const hoursToAdd = hourOffset * 60 * 60 * 1000;
		endDate.setTime( endDate.getTime() + hoursToAdd );
		return endDate.toISOString();
	};

	if ( ! meta._event_start ) {
		setMeta( { _event_start: getNow( 1 ) } );
	}

	if ( ! meta._event_end ) {
		setMeta( { _event_end: getNow( 2 ) } );
	}

	return (
		<PluginDocumentSettingPanel
			name="events-datetime-settings"
			title={ __( 'Time and Date', 'events' ) }
			className="events-datetime-settings"
		>
			<TextControl
				label={ __( 'Starts at', 'events' ) }
				value={ meta._event_start }
				onChange={ ( value ) => {
					setMeta( { _event_start: value } );
					if ( ! compareTime( value, meta._event_end ) ) {
						let endDate = new Date( value );
						setMeta( { _event_end: endDate.toISOString() } );
					}
					if ( ! meta._event_rsvp_start || compareTime( meta._event_rsvp_start, value ) ) {
						let startDate = new Date( value );
						setMeta( { _event_rsvp_start: startDate.toISOString() } );
					}
				} }
				min={ getNow( 1 ) }
				max={ meta._event_end }
				step={ 300 }
				name="em-from-date"
				type="datetime-local"
			/>

			<TextControl
				label={ __( 'Ends at', 'events' ) }
				value={ meta._event_end }
				onChange={ ( value ) => {
					setMeta( { _event_end: value } );

					if ( !! meta._event_rsvp_end || ! compareTime( value, meta._event_rsvp_end ) ) {
						let endDate = new Date( value );
						setMeta( { _event_rsvp_end: endDate.toISOString() } );
					}
				} }
				step={ 300 }
				min={ meta._event_start }
				name="em-to-date"
				type="datetime-local"
			/>

			<CheckboxControl
				checked={ meta._event_all_day == 1 }
				onChange={ ( value ) => {
					setMeta( { _event_all_day: value ? 1 : 0 } );
				} }
				label={ __( 'All day', 'events' ) }
			/>
		</PluginDocumentSettingPanel>
	);
};

export default datetimeSelector;
