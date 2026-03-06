import { __ } from '@wordpress/i18n';
import type { FormField as FormFieldType } from '../types';

type Props = {
	field: FormFieldType;
	value: unknown;
	error?: string;
	onChange: (value: unknown) => void;
};

export function FormField({ field, value, error, onChange }: Props) {
	const id = `booking-field-${field.name}`;
	const baseClass = 'booking-form__field';

	return (
		<div
			className={`${baseClass} ${baseClass}--width-${field.width}`}
			data-field={field.name}
		>
			{field.type !== 'html' && field.type !== 'checkbox' && (
				<label className={`${baseClass}-label`} htmlFor={id}>
					{field.label}
					{field.required && <span aria-hidden="true"> *</span>}
				</label>
			)}

			{field.description && (
				<p className={`${baseClass}-description`}>{field.description}</p>
			)}

			{renderInput(field, id, value, onChange)}

			{error && (
				<span className={`${baseClass}-error`} role="alert">
					{error}
				</span>
			)}
		</div>
	);
}

function renderInput(
	field: FormFieldType,
	id: string,
	value: unknown,
	onChange: (value: unknown) => void,
) {
	switch (field.type) {
		case 'textarea':
			return (
				<textarea
					id={id}
					name={field.name}
					required={field.required}
					value={String(value ?? '')}
					onChange={(event) => onChange(event.target.value)}
					className="booking-form__textarea"
				/>
			);

		case 'select': {
			const options = (field.options as Array<{ value: string; label: string }>) ?? [];
			return (
				<select
					id={id}
					name={field.name}
					required={field.required}
					value={String(value ?? '')}
					onChange={(event) => onChange(event.target.value)}
					className="booking-form__select"
				>
					<option value="">{__('Please select…', 'ctx-events')}</option>
					{options.map((option) => (
						<option key={option.value} value={option.value}>
							{option.label}
						</option>
					))}
				</select>
			);
		}

		case 'checkbox':
			return (
				<label className="booking-form__checkbox-label" htmlFor={id}>
					<input
						id={id}
						type="checkbox"
						name={field.name}
						required={field.required}
						checked={Boolean(value)}
						onChange={(event) => onChange(event.target.checked)}
						className="booking-form__checkbox"
					/>
					<span
						// eslint-disable-next-line react/no-danger
						dangerouslySetInnerHTML={{ __html: String(field.label) }}
					/>
				</label>
			);

		case 'html':
			return (
				<div
					className="booking-form__html"
					// eslint-disable-next-line react/no-danger
					dangerouslySetInnerHTML={{ __html: String(field.content ?? '') }}
				/>
			);

		case 'number':
			return (
				<input
					id={id}
					type="number"
					name={field.name}
					required={field.required}
					value={String(value ?? '')}
					min={field.min as number | undefined}
					max={field.max as number | undefined}
					step={field.step as number | undefined}
					onChange={(event) => onChange(event.target.valueAsNumber)}
					className="booking-form__input"
				/>
			);

		case 'date':
			return (
				<input
					id={id}
					type="date"
					name={field.name}
					required={field.required}
					value={String(value ?? '')}
					min={field.minDate as string | undefined}
					max={field.maxDate as string | undefined}
					onChange={(event) => onChange(event.target.value)}
					className="booking-form__input"
				/>
			);

		default:
			// input, country, phone, email, tel, url
			return (
				<input
					id={id}
					type={field.type === 'country' ? 'text' : String(field.inputType ?? 'text')}
					name={field.name}
					required={field.required}
					value={String(value ?? '')}
					onChange={(event) => onChange(event.target.value)}
					className="booking-form__input"
				/>
			);
	}
}
