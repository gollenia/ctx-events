/**
 * Adds a metabox for the page color settings
 */

/**
 * WordPress dependencies
 */
import { SelectControl, TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { select } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

const personalData = () => {
	const postType = select('core/editor').getCurrentPostType();

	if (postType !== 'event-speaker') return null;

	const [meta, setMeta] = useEntityProp('postType', postType, 'meta');

	return (
		<PluginDocumentSettingPanel
			name="events-location-settings"
			title={__('Personal Information', 'ctx-events')}
			className="events-location-settings"
		>
			<SelectControl
				label={__('Gender', 'ctx-events')}
				value={meta._gender}
				onChange={(value) => {
					setMeta({ _gender: value });
				}}
				options={[
					{
						label: __('Male', 'ctx-events'),
						value: 'male',
					},
					{
						label: __('Female', 'ctx-events'),
						value: 'female',
					},
				]}
			/>

			<TextControl
				type="date"
				label={__('Birthday', 'ctx-events')}
				value={meta._birthday}
				onChange={(value) => {
					setMeta({ _birthday: value });
				}}
			/>
		</PluginDocumentSettingPanel>
	);
};

export default personalData;
