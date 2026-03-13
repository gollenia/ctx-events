import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { FormFieldRenderer } from '../components/FormFieldRenderer';
import { buildInitialFormValues } from '../formFields';
import { isFieldVisible } from '../hooks/useFieldVisibility';
import type { BookingFormData } from '../types';

type Props = {
	bookingForm: BookingFormData;
	initialData: Record<string, unknown>;
	onNext: (data: Record<string, unknown>) => void;
};

function validate(
	form: BookingFormData,
	formData: Record<string, unknown>,
): Record<string, string> {
	const errors: Record<string, string> = {};

	for (const field of form.fields) {
		if (!isFieldVisible(field.visibilityRule, formData)) continue;
		if (!field.required) continue;

		const val = formData[field.name];
		const isEmpty =
			val === undefined || val === null || val === '' || val === false;
		if (isEmpty) {
			errors[field.name] = __('This field is required.', 'ctx-events');
		}
	}

	return errors;
}

export function BookingFormSection({
	bookingForm,
	initialData,
	onNext,
}: Props) {
	const [formData, setFormData] =
		useState<Record<string, unknown>>(() =>
			buildInitialFormValues(bookingForm.fields, initialData),
		);
	const [errors, setErrors] = useState<Record<string, string>>({});

	function handleChange(name: string, value: unknown) {
		setFormData((prev) => ({ ...prev, [name]: value }));
		setErrors((prev) => ({ ...prev, [name]: '' }));
	}

	function handleSubmit() {
		const errs = validate(bookingForm, formData);
		if (Object.keys(errs).length > 0) {
			setErrors(errs);
			return;
		}
		onNext(formData);
	}

	return (
		<div className="booking-section booking-section--booking-form">
			<FormFieldRenderer
				fields={bookingForm.fields}
				formData={formData}
				errors={errors}
				onChange={handleChange}
			/>

			<div className="booking-section__footer">
				<button
					type="button"
					className="booking-btn booking-btn--primary"
					onClick={handleSubmit}
				>
					{__('Continue', 'ctx-events')}
				</button>
			</div>
		</div>
	);
}
