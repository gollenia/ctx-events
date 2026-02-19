import { TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

const LocationAddress = () => {
	const postType = useSelect(
		(select) => select('core/editor').getCurrentPostType(),
		[],
	);
	if (postType !== 'ctx-event-location') return null;

	const [meta, setMeta] = useEntityProp('postType', postType, 'meta');

	return (
		<PluginDocumentSettingPanel
			name="events-location-address-settings"
			title={__('Address', 'ctx-events')}
			className="events-location-address-settings"
		>
			<TextControl
				label={__('Street Address', 'ctx-events')}
				value={meta._location_address}
				onChange={(value) => {
					setMeta({ _location_address: value });
				}}
			/>

			<TextControl
				label={__('City', 'ctx-events')}
				value={meta._location_town}
				onChange={(value) => {
					setMeta({ _location_town: value });
				}}
			/>

			<TextControl
				label={__('State/Province', 'ctx-events')}
				value={meta._location_state}
				onChange={(value) => {
					setMeta({ _location_state: value });
				}}
			/>

			<TextControl
				label={__('Postal/Zip Code', 'ctx-events')}
				value={meta._location_postcode}
				onChange={(value) => {
					setMeta({ _location_postcode: value });
				}}
			/>

			<TextControl
				label={__('Country', 'ctx-events')}
				value={meta._location_country}
				onChange={(value) => {
					setMeta({ _location_country: value });
				}}
			/>
		</PluginDocumentSettingPanel>
	);
};

export default LocationAddress;
