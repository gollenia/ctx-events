import InputField from './InputField';
import {
	isFieldVisible,
	normalizeFieldValue,
	type FieldValue,
	type FormFieldDefinition,
	type FormState,
} from '../form-core';

type Props = {
	fields: FormFieldDefinition[];
	formData: Record<string, unknown>;
	errors?: Record<string, string>;
	status?: FormState;
	disabled?: boolean;
	formTouched?: boolean;
	onChange: (name: string, value: FieldValue) => void;
};

export function FormFields({
	fields,
	formData,
	errors = {},
	status = 'LOADED',
	disabled = false,
	formTouched = false,
	onChange,
}: Props) {
	const visibleFields = fields.filter((field) =>
		isFieldVisible(field.visibilityRule, formData),
	);

	return (
		<div className="ctx-form-fields">
			{visibleFields.map((field) => (
				<div
					key={field.name}
					className={`ctx-form-fields__item ctx-width--${field.width ?? 6}`}
					data-testid={field.testId}
				>
					{field.description && (
						<p className="ctx-form-field-description">{field.description}</p>
					)}
					<InputField
						{...field}
						status={status}
						disabled={disabled}
						formTouched={formTouched}
						error={errors[field.name]}
						value={normalizeFieldValue(field, formData)}
						onChange={(value) => onChange(field.name, value)}
					/>
				</div>
			))}
		</div>
	);
}
