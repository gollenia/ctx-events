import apiFetch from '@wordpress/api-fetch';
import { DataViews, filterSortAndPaginate } from '@wordpress/dataviews/wp';
import { __ } from '@wordpress/i18n';
import { check, closeSmall, Icon, trash } from '@wordpress/icons';
import React, { useEffect, useMemo, useState } from 'react';
import GatewayModal from './GatewayModal';

const GatewayTable = () => {
	const [ gateways, setGateways ] = React.useState( [] );
	const [ loading, setLoading ] = React.useState( true );
	const [ error, setError ] = React.useState( null );
	const [ slug, setSlug ] = React.useState( '' );

	const [ view, setView ] = useState( {
		type: 'table',
		search: '',
		page: 1,
		perPage: 100,
		fields: [ 'description', 'active' ],
		layout: {},
		filters: [],
		titleField: 'title',
		sort: {
			order: 'asc',
			orderby: 'title',
		},
	} );

	const fields = [
		{
			label: __( 'Title', 'events' ),
			id: 'title',
			enableHiding: false,
			enableGlobalSearch: true,
			type: 'string',
		},
		{
			label: __( 'Description', 'events' ),
			id: 'description',
			enableSorting: false,
			enableGlobalSearch: true,
			type: 'string',
		},
		{
			label: __( 'Active', 'events' ),
			id: 'active',
			render: ( { item } ) => {
				return item.active ? <Icon icon={ check } /> : <Icon icon={ closeSmall } />;
			},

			enableSorting: false,
		},
	];

	const { data: shownData, paginationInfo } = useMemo( () => {
		return filterSortAndPaginate( gateways, view, fields );
	}, [ view, gateways ] );

	useEffect( () => {
		apiFetch( { path: '/events/v2/gateways' } )
			.then( ( data ) => {
				setGateways( data );
				setLoading( false );
			} )
			.catch( ( err ) => {
				setError( err.message );
				setLoading( false );
			} );
	}, [] );

	const onToggle = ( slug ) => {
		apiFetch( { path: `/events/v2/gateway/toggle`, method: 'POST', data: { slug } } )
			.then( ( data ) => {
				console.log( data );
				setGateways( ( prev ) => {
					const updatedGateways = prev.map( ( gateway ) => {
						if ( gateway.slug === slug ) {
							return {
								...gateway,
								active: ! gateway.active,
							};
						}
						return gateway;
					} );
					return updatedGateways;
				} );
				setLoading( false );
			} )
			.catch( ( err ) => {
				setError( err.message );
				setLoading( false );
			} );
	};

	const onCancel = () => {
		setSlug( '' );
	};

	if ( loading ) {
		return <div>Loading...</div>;
	}

	if ( error ) {
		return <div>Error: { error }</div>;
	}
	return (
		<div>
			<DataViews
				data={ shownData }
				view={ view }
				onChangeView={ setView }
				paginationInfo={ paginationInfo }
				// You can define custom columns if needed
				defaultLayouts={ {
					table: {
						// Define default table layout settings
						spacing: 'normal',
						showHeader: true,
					},
				} }
				fields={ fields }
				actions={ [
					{
						id: 'toggle',
						label: ( [ item ] ) => {
							return item.active ? __( 'Deactivate', 'events' ) : __( 'Activate', 'events' );
						},
						isDestructive: true,
						icon: trash,
						callback: async ( [ item ] ) => {
							onToggle( item.slug );
						},
					},
					{
						id: 'settings',
						label: __( 'Settings', 'events' ),
						icon: trash,
						callback: async ( [ item ] ) => {
							setSlug( item.slug );
						},
					},
				] }
			/>
			<GatewayModal slug={ slug } onCancel={ onCancel } />
		</div>
	);
};

export default GatewayTable;
