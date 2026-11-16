import type { FormField as FormFieldType } from '../types';
import {
	InputField,
	isFieldVisible,
	normalizeFieldValue,
	sanitizeInlineHtml,
} from '@contexis/wp-react-form';
import { getBookingFieldDefinition } from '../formFields';

type Props = {
	fields: FormFieldType[];
	formData: Record<string, unknown>;
	errors: Record<string, string>;
	onChange: (name: string, value: unknown) => void;
};

export function FormFieldRenderer({
	fields,
	formData,
	errors,
	onChange,
}: Props) {
	return (
		<div className="ctx-form-fields">
			{fields
				.map(getBookingFieldDefinition)
				.filter((field) => isFieldVisible(field.visibilityRule, formData))
				.map((field) => (
					<div
						className={`ctx-form-fields__item ctx-width--${field.width ?? 6}`}
						data-testid={field.testId}
						key={field.name}
					>
						{field.description &&
							(field.type === 'checkbox' || field.type === 'toggle' ? (
								<div
									className="ctx-form-field-description"
									dangerouslySetInnerHTML={{
										__html: sanitizeInlineHtml(field.description),
									}}
								/>
							) : (
								<p className="ctx-form-field-description">
									{field.description}
								</p>
							))}
						<InputField
							{...field}
							status="LOADED"
							error={errors[field.name]}
							value={normalizeFieldValue(field, formData)}
							onChange={(value) => onChange(field.name, value)}
						/>
					</div>
				))}
		</div>
	);
}
