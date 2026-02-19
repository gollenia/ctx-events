/**
 * Wordpress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import {
	Flex,
	FlexItem,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const postType = props.context.postType;
	if (postType !== 'ctx-event-location') return null;
	const [meta, setMeta] = useEntityProp('postType', postType, 'meta');

	const blockProps = useBlockProps({
		className: ['location-edit'].filter(Boolean).join(' '),
	});

	const COUNTRY_CODES = [
		'US',
		'CA',
		'GB',
		'DE',
		'FR',
		'IT',
		'ES',
		'NL',
		'AU',
		'JP',
		'CN',
		'IN',
	];

	const makeCountryOptions = (locale = 'de') => {
		let regionNames;
		if (typeof Intl !== 'undefined' && Intl.DisplayNames) {
			regionNames = new Intl.DisplayNames([locale], { type: 'region' });
		}

		return [
			{ value: '', label: __('Select Country', 'ctx-events') },
			...COUNTRY_CODES.map((code) => ({
				value: code,
				label: regionNames ? regionNames.of(code) : code,
			})),
		];
	};

	const countries = useMemo(() => makeCountryOptions('de'), []);

	return (
		<div>
			<div className="location-edit__admin ctx-block-editor">
				<TextControl
					label={__('Address', 'ctx-events')}
					value={meta._location_address}
					onChange={(value) => {
						setMeta({
							...meta,
							_location_address: value,
						});
					}}
				/>
				<Flex>
					<FlexItem isBlock>
						<TextControl
							label={__('ZIP Code', 'ctx-events')}
							value={meta._location_postcode}
							onChange={(value) => {
								setMeta({
									...meta,
									_location_postcode: value,
								});
							}}
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('City', 'ctx-events')}
							value={meta._location_town}
							onChange={(value) => {
								setMeta({
									...meta,
									_location_town: value,
								});
							}}
						/>
					</FlexItem>
				</Flex>

				<SelectControl
					label={__('Country', 'ctx-events')}
					value={meta._location_country}
					options={countries}
					onChange={(value) => {
						setMeta({
							...meta,
							_location_country: value,
						});
					}}
				/>

				<TextControl
					label={__('State', 'ctx-events')}
					value={meta._location_state}
					onChange={(value) => {
						setMeta({
							...meta,
							_location_state: value,
						});
					}}
				/>

				<TextControl
					label={__('URL', 'ctx-events')}
					value={meta._location_url}
					onChange={(value) => {
						setMeta({
							...meta,
							_location_url: value,
						});
					}}
				/>
			</div>
		</div>
	);
};

export default edit;
