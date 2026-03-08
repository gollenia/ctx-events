import type { FormField as FormFieldType } from '../types';
import { isFieldVisible } from '../../../shared/__experimentalForm';
import { FormField } from './FormField';

type Props = {
	fields: FormFieldType[];
	formData: Record<string, unknown>;
	errors: Record<string, string>;
	onChange: (name: string, value: unknown) => void;
};

export function FormFieldRenderer({ fields, formData, errors, onChange }: Props) {
	return (
		<div className="booking-form__fields">
			{fields.map((field) => {
				if (!isFieldVisible(field.visibilityRule, formData)) return null;

				return (
					<FormField
						key={field.name}
						field={field}
						value={formData[field.name]}
						error={errors[field.name]}
						onChange={(value) => onChange(field.name, value)}
					/>
				);
			})}
		</div>
	);
}
