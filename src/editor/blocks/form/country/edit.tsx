/**
 * Wordpress dependencies
 */

import {
	FieldHeader,
	useFieldName,
	useFieldProps,
	type VisibilityRule,
} from '@events/form';
import { getCountriesByRegion } from '@events/i18n';
import { SelectControl } from '@wordpress/components';
/**
 * Internal dependencies
 */
import icon from './icon';
import Inspector from './inspector';

type CountryAttributes = {
	name: string;
	context: string;
	width: number;
	required: boolean;
	label: string;
	defaultValue: string;
	description?: string;
	region: string;
	allowedCountries?: string[];
	visibilityRule?: VisibilityRule | null;
};

interface EditProps {
	attributes: CountryAttributes;
	setAttributes: (attributes: Partial<CountryAttributes>) => void;
	clientId: string;
}

const edit = (props: EditProps) => {
	const { attributes, setAttributes } = props;

	useFieldName(attributes, setAttributes);

	const { defaultValue, region } = attributes;

	const locale = document.documentElement.lang;

	const countryCodes = getCountriesByRegion(region);
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
			/>

			<SelectControl
				onChange={(event) => {
					setAttributes({ defaultValue: event });
				}}
				className="ctx:event-field__input"
				value={defaultValue}
			>
				{countryCodes.map((country) => {
					if (!country) return null;
					return (
						<option key={country} value={country}>
							{regionNames.of(country)}
						</option>
					);
				})}
			</SelectControl>
		</div>
	);
};

export default edit;
