import apiFetch from '@wordpress/api-fetch';
import { CheckboxControl, ComboboxControl, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import React, { useEffect, useState } from 'react';

const AdminField = ( { type, label, value, onChange, help, error, required, ...props } ) => {
	const [ countries, setCountries ] = useState( [] );
	const [ pages, setPages ] = useState( [] );
	const [ loading, setLoading ] = useState( true );
	const browserLanguage = navigator.language.split( '-' )[ 0 ];

	useEffect( () => {
		if ( type !== 'country' ) {
			return;
		}
		fetch( `https://countries.kids-team.com/countries/${ props.region ?? 'world' }/${ browserLanguage }` )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				const countryList = Object.entries( data ).map( ( [ key, name ], index ) => {
					return { value: key, label: name };
				} );

				setCountries( countryList );
			} );
	}, [] );

	useEffect( () => {
		if ( type !== 'page_select' ) {
			return;
		}
		apiFetch( { path: '/wp/v2/pages' } )
			.then( ( data ) => {
				setPages( data );
				setLoading( false );
			} )
			.catch( ( err ) => {
				console.error( 'Fehler beim Laden der Seiten:', err );
				setPages( [] );
				setLoading( false );
			} );
	}, [] );

	console.log( 'AdminField', type, label );

	if ( type === 'textarea' ) {
		return (
			<TextareaControl
				label={ label }
				value={ value }
				onChange={ onChange }
				help={ help }
				error={ error }
				required={ required }
				{ ...props }
			/>
		);
	}

	if ( type === 'select' || type === 'radio' ) {
		const mappedOptions = props.options.map( ( option ) => {
			return { label: option, value: option };
		} );

		return (
			<SelectControl
				label={ label }
				value={ value }
				onChange={ onChange }
				onFocus={ onChange }
				help={ help }
				error={ error }
				required={ required }
				options={ mappedOptions }
				defaultValue={ mappedOptions[ 0 ].value }
			/>
		);
	}

	if ( type === 'country' ) {
		return (
			<SelectControl
				label={ label }
				value={ value }
				onChange={ onChange }
				help={ help }
				error={ error }
				required={ required }
				options={ countries }
			/>
		);
	}

	if ( type === 'checkbox' ) {
		return (
			<CheckboxControl
				label={ help ?? label }
				value={ value }
				onChange={ onChange }
				required={ required }
				checked={ value }
				type="checkbox"
			/>
		);
	}

	if ( type === 'date' ) {
		return (
			<TextControl
				label={ label }
				value={ value }
				onChange={ onChange }
				help={ help }
				error={ error }
				type="date"
				required={ required }
				{ ...props }
			/>
		);
	}

	if ( type === 'page_select' ) {
		return (
			<ComboboxControl
				label={ label }
				value={ value }
				onChange={ onChange }
				help={ help }
				error={ error }
				required={ required }
				disabled={ loading }
				options={ pages.map( ( option ) => ( {
					label: option.title.rendered,
					value: option.id,
				} ) ) }
			/>
		);
	}

	return (
		<TextControl
			label={ label }
			value={ value }
			onChange={ onChange }
			help={ help }
			error={ error }
			type={ type }
			required={ required }
			{ ...props }
		/>
	);
};

export default AdminField;
