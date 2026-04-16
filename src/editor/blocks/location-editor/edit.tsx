import { getCountries } from '@events/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import {
	ComboboxControl,
	Flex,
	FlexItem,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import type { ComboboxControlOption } from '@wordpress/components/build-types/combobox-control/types';
import { useEntityProp } from '@wordpress/core-data';
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

type LocationMeta = {
	_location_address?: string;
	_location_postcode?: string;
	_location_town?: string;
	_location_country?: string;
	_location_state?: string;
	_location_url?: string;
};

type EditProps = {
	context: {
		postType?: string;
	};
};

const COUNTRY_CODES = getCountries();

function makeCountryOptions(locale = 'de'): ComboboxControlOption[] {
	let regionNames: Intl.DisplayNames | undefined;

	if (typeof Intl !== 'undefined' && Intl.DisplayNames) {
		regionNames = new Intl.DisplayNames([locale], { type: 'region' });
	}

	return [
		{ value: '', label: __('Select Country', 'ctx-events') },
		...COUNTRY_CODES.map<ComboboxControlOption>((code) => ({
			value: code,
			label: regionNames?.of(code) ?? code,
		})),
	];
}

const edit = (props: EditProps) => {
	const postType = props.context.postType;

	if (postType !== 'ctx-event-location') {
		return null;
	}

	const [meta, setMeta] = useEntityProp('postType', postType, 'meta') as [
		LocationMeta,
		(value: LocationMeta) => void,
		unknown,
	];

	const blockProps = useBlockProps({
		className: 'location-edit',
	});

	const countries = useMemo(() => makeCountryOptions('de'), []);

	return (
		<div {...blockProps}>
			<div className="location-edit__admin ctx-block-editor">
				<TextControl
					label={__('Address', 'ctx-events')}
					value={meta._location_address ?? ''}
					onChange={(value) => {
						setMeta({
							...meta,
							_location_address: value ?? undefined,
						});
					}}
				/>
				<Flex>
					<FlexItem isBlock>
						<TextControl
							label={__('ZIP Code', 'ctx-events')}
							value={meta._location_postcode ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_location_postcode: value ?? undefined,
								});
							}}
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('City', 'ctx-events')}
							value={meta._location_town ?? ''}
							onChange={(value) => {
								setMeta({
									...meta,
									_location_town: value ?? undefined,
								});
							}}
						/>
					</FlexItem>
				</Flex>

				<ComboboxControl
					label={__('Country', 'ctx-events')}
					value={meta._location_country ?? ''}
					options={countries}
					onChange={(value) => {
						setMeta({
							...meta,
							_location_country: value ?? undefined,
						});
					}}
				/>

				<TextControl
					label={__('State', 'ctx-events')}
					value={meta._location_state ?? ''}
					onChange={(value) => {
						setMeta({
							...meta,
							_location_state: value ?? undefined,
						});
					}}
				/>

				<TextControl
					label={__('URL', 'ctx-events')}
					value={meta._location_url ?? ''}
					onChange={(value) => {
						setMeta({
							...meta,
							_location_url: value ?? undefined,
						});
					}}
				/>
			</div>
		</div>
	);
};

export default edit;
