import type { FormField as FormFieldType } from '../types';
import type { FormFieldDefinition, FieldValue, InputType } from '../../../shared/__experimentalForm';
import { InputField } from '../../../shared/__experimentalForm';

type Props = {
	field: FormFieldType;
	value: unknown;
	error?: string;
	onChange: (value: unknown) => void;
};

function toFieldDefinition(field: FormFieldType): FormFieldDefinition {
	const base = {
		name: field.name,
		label: field.label,
		width: field.width,
		required: field.required,
		visibilityRule: field.visibilityRule,
	};

	switch (field.type) {
		case 'input':
			return { ...base, type: (field.inputType as InputType) ?? 'text' };

		case 'date':
			return {
				...base,
				type: 'date',
				min: field.minDate as unknown as number,
				max: field.maxDate as unknown as number,
			};

		case 'number':
			return {
				...base,
				type: 'number',
				min: field.min as number | undefined,
				max: field.max as number | undefined,
			};

		case 'select': {
			const rawOptions = (field.options as Array<{ value: string; label: string }>) ?? [];
			const options: Record<string, string> = Object.fromEntries(
				rawOptions.map((opt) => [opt.value, opt.label]),
			);
			return { ...base, type: 'select', options, hasEmptyOption: true };
		}

		case 'country':
			return { ...base, type: 'country', region: 'world' };

		case 'html':
			return { ...base, type: 'html', content: field.content as string | undefined };

		default:
			return { ...base, type: field.type as InputType };
	}
}

export function FormField({ field, value, error, onChange }: Props) {
	const definition = toFieldDefinition(field);

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
