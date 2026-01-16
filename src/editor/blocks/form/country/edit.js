/**
 * Wordpress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import icon from './icon.js';
import Inspector from './inspector.js';
import { FieldHeader, useFieldProps } from '@events/form'
import { getCountriesByRegion } from '@events/i18n';

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const {
		attributes,
		setAttributes,
	} = props;

	const { defaultValue, region } = attributes;

	const locale = document.documentElement.lang

	const countryCodes = getCountriesByRegion(region)
	const regionNames = new Intl.DisplayNames([locale], { type: 'region' });

	const blockProps = useFieldProps(attributes);

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<FieldHeader
				attributes={attributes}
				setAttributes={setAttributes}
				clientId={props.clientId}
				icon={icon}
				helpText={__('Select a country', 'events')}
			/>
			
			<select
				onChange={(event) => {
					setAttributes({ defaultValue: event.target.value });
				}}
				className="ctx:event-field__input"
				value={defaultValue}
			>
				{countryCodes.map((country) => {
					if (!country) return null;
					return (
						<option
							key={country}
							value={country}
						>
							{regionNames.of(country)}
						</option>
					);
				})}
			</select>
		</div>
	);
};

export default edit;
