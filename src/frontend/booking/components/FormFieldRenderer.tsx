import type { FormField as FormFieldType } from '../types';
import { FormFields } from '../../../shared/__experimentalForm';
import { getBookingFieldDefinition } from '../formFields';

type Props = {
	fields: FormFieldType[];
	formData: Record<string, unknown>;
	errors: Record<string, string>;
	onChange: (name: string, value: unknown) => void;
};

export function FormFieldRenderer({ fields, formData, errors, onChange }: Props) {
	return (
		<FormFields
			fields={fields.map(getBookingFieldDefinition)}
			formData={formData}
			errors={errors}
			onChange={(name, value) => onChange(name, value)}
		/>
	);
}
