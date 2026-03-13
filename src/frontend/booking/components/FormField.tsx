import type { FormField as FormFieldType } from '../types';
import type { FieldValue } from '../../../shared/__experimentalForm';
import { InputField } from '../../../shared/__experimentalForm';
import { getBookingFieldDefinition } from '../formFields';

type Props = {
	field: FormFieldType;
	value: unknown;
	error?: string;
	onChange: (value: unknown) => void;
};

export function FormField({ field, value, error, onChange }: Props) {
	const definition = getBookingFieldDefinition(field);

	return (
		<div className={`booking-form__field booking-form__field--width-${field.width}`}>
			{field.description && (
				<p className="booking-form__field-description">{field.description}</p>
			)}
			<InputField
				{...definition}
				status="LOADED"
				formTouched={false}
				disabled={false}
				value={value as FieldValue}
				error={error}
				onChange={onChange as (value: FieldValue) => void}
			/>
		</div>
	);
}
