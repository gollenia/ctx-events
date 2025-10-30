/**
 * Wordpress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import { Flex, FlexItem, SelectControl, TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = ( props ) => {
	const { context } = props;

	const postType = useSelect( ( select ) => select( 'core/editor' ).getCurrentPostType(), [] );
	if ( postType !== 'location' ) return <></>;
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	const blockProps = useBlockProps( {
		className: [ 'location-edit' ].filter( Boolean ).join( ' ' ),
	} );

	const [ countries, setCountries ] = useState( [] );

	useEffect( () => {
		if ( ! meta._location_country ) {
			setMeta( {
				...meta,
				_location_country: window.EM.country,
			} );
		}
	}, [] );

	const fetchCountries = async () => {
		const response = await fetch( 'https://countries.kids-team.com/countries/world/' + 'de' );
		const data = await response.json();
		const items = Object.entries( data ).map( ( [ key, value ] ) => {
			return {
				value: key,
				label: value,
			};
		} );
		items.unshift( {
			value: '',
			label: __( 'Select Country', 'events' ),
		} );
		setCountries( items );
	};

	useEffect( () => {
		fetchCountries();
		if ( ! meta._location_country ) {
			{
				countries.map( ( country, index ) => {
					if ( ! country ) return <></>;
					return (
						<option key={ index } value={ country.value } selected={ placeholder == country.value }>
							{ country.label }
						</option>
					);
				} );
			}
		}
	}, [] );

	return (
		<div>
			<div className="location-edit__admin">
				<TextControl
					label={ __( 'Address', 'events' ) }
					value={ meta._location_address }
					onChange={ ( value ) => {
						setMeta( {
							...meta,
							_location_address: value,
						} );
					} }
				/>
				<Flex>
					<FlexItem isBlock>
						<TextControl
							label={ __( 'ZIP Code', 'events' ) }
							value={ meta._location_postcode }
							onChange={ ( value ) => {
								setMeta( {
									...meta,
									_location_postcode: value,
								} );
							} }
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={ __( 'City', 'events' ) }
							value={ meta._location_town }
							onChange={ ( value ) => {
								setMeta( {
									...meta,
									_location_town: value,
								} );
							} }
						/>
					</FlexItem>
				</Flex>

				<SelectControl
					label={ __( 'Country', 'events' ) }
					value={ meta._location_country }
					options={ countries }
					onChange={ ( value ) => {
						setMeta( {
							...meta,
							_location_country: value,
						} );
					} }
				/>

				<TextControl
					label={ __( 'State', 'events' ) }
					value={ meta._location_state }
					onChange={ ( value ) => {
						setMeta( {
							...meta,
							_location_state: value,
						} );
					} }
				/>

				<TextControl
					label={ __( 'URL', 'events' ) }
					value={ meta._location_url }
					onChange={ ( value ) => {
						setMeta( {
							...meta,
							_location_url: value,
						} );
					} }
				/>
			</div>
		</div>
	);
};

export default edit;
