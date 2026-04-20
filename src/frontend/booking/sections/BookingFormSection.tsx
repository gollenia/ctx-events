import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@contexis/wp-react-form';
import { FormFieldRenderer } from '../components/FormFieldRenderer';
import { SectionFooter } from '../components/SectionFooter';
import { buildInitialFormValues } from '../formFields';
import type { BookingFormData } from '../types';
import { isBookingFormComplete, validateBookingFormData } from '../validation';

type Props = {
	bookingForm: BookingFormData;
	initialData: Record<string, unknown>;
	onNext: (data: Record<string, unknown>) => void;
};

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
		const errs = validateBookingFormData(bookingForm, formData);
		if (Object.keys(errs).length > 0) {
			setErrors(errs);
			return;
		}
		onNext(formData);
	}

	const canContinue = isBookingFormComplete(bookingForm, formData);

	return (
		<div
			className="booking-section booking-section--booking-form"
			data-testid="booking-section-registration"
		>
			<FormFieldRenderer
				fields={bookingForm.fields}
				formData={formData}
				errors={errors}
				onChange={handleChange}
			/>

			<SectionFooter>
				<Button
					onClick={handleSubmit}
					disabled={!canContinue}
					data-testid="booking-registration-continue"
				>
					{__('Continue', 'ctx-events')}
				</Button>
			</SectionFooter>
		</div>
	);
}
