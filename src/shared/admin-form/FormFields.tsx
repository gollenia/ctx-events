import type {
	FieldValue,
	FormFieldDefinition,
	FormState,
} from '@contexis/wp-react-form';
import {
	isFieldVisible,
	normalizeFieldValue,
} from '@contexis/wp-react-form';
import InputField from './InputField';

type Props = {
	fields: FormFieldDefinition[];
	formData: Record<string, unknown>;
	errors?: Record<string, string>;
	status?: FormState;
	disabled?: boolean;
	formTouched?: boolean;
	onChange: (name: string, value: FieldValue) => void;
};

const FormFields = ({
	fields,
	formData,
	errors = {},
	status = 'LOADED',
	disabled = false,
	formTouched = false,
	onChange,
}: Props) => {
	const visibleFields = fields.filter((field) =>
		isFieldVisible(field.visibilityRule, formData),
	);

	return (
		<>
			{visibleFields.map((field) => (
				<InputField
					key={field.name}
					{...field}
					status={status}
					disabled={disabled}
					formTouched={formTouched}
					error={errors[field.name]}
					value={normalizeFieldValue(field, formData)}
					onChange={(value) => onChange(field.name, value)}
				/>
			))}
		</>
	);
};

export default FormFields;
