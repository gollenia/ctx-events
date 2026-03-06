import { useOtherFormFields } from '@events/form';
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

type VisibilityRuleValue = 'checked' | 'unchecked';

export type VisibilityRule = Readonly<{
	field: string;
	value: VisibilityRuleValue;
}>;

interface VisibilityRulesProps {
	clientId: string;
	visibilityRule: VisibilityRule | null;
	onChange: (rule: VisibilityRule | null) => void;
}

const allowedFieldTypes = [
	'ctx-events/form-checkbox',
	'ctx-events/form-radio',
	'ctx-events/form-select',
];

const VisibilityRules = ({
	clientId,
	visibilityRule,
	onChange,
}: VisibilityRulesProps): React.ReactElement => {
	const availableFields = [
		{ label: __('Select a field', 'ctx-events'), value: '' },
		...useOtherFormFields(clientId).filter((field) =>
			allowedFieldTypes.includes(field.type),
		),
	];

	const handleFieldChange = (field: string): void => {
		onChange(
			field === ''
				? null
				: { field, value: visibilityRule?.value ?? 'checked' },
		);
	};

	const handleValueChange = (value: string): void => {
		if (
			!visibilityRule?.field ||
			(value !== 'checked' && value !== 'unchecked')
		) {
			return;
		}
		onChange({ field: visibilityRule.field, value });
	};

	return (
		<>
			<SelectControl
				label={__('Field', 'ctx-events')}
				value={visibilityRule?.field ?? ''}
				options={availableFields}
				onChange={handleFieldChange}
			/>
			<SelectControl
				label={__('Value', 'ctx-events')}
				value={visibilityRule?.value ?? 'checked'}
				disabled={!visibilityRule?.field}
				options={[
					{ label: __('Checked', 'ctx-events'), value: 'checked' },
					{ label: __('Unchecked', 'ctx-events'), value: 'unchecked' },
				]}
				onChange={handleValueChange}
			/>
		</>
	);
};

export default VisibilityRules;
