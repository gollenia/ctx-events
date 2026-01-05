
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useOtherFormFields } from '@events/form';
	
const VisibilityRules = (props) => {
	console.log(props)
	const {
		clientId,
		attributes,
		setAttributes,
	} = props;

	const { visibilityRule } = attributes;
	const availableFields = useOtherFormFields(clientId);
	
	const setSuperiorField = (field) => {
		if (field === '') {
			setAttributes({ visibilityRule: null });
			return;
		}
		setAttributes({
			visibilityRule: {
				...(visibilityRule || {}),
				field: field
			},
		});
	}


	return (
		<SelectControl
			label={__('Field', 'events')}
			value={visibilityRule?.field || ''}
			options={availableFields}
			onChange={(value) => setSuperiorField(value)}
		/>
	)
}

export default VisibilityRules;