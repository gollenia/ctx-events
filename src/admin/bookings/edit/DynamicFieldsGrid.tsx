import type { BookingFormField } from 'src/types/types';
import { InputField } from '../../../shared/admin-form';
import { isFieldVisible } from '../../../shared/form-core';
import {
	type BookingFormValues,
	getBookingFieldDefinition,
	getBookingFieldValue,
} from './formFields';

type Props = {
	fields: BookingFormField[];
	values: BookingFormValues;
	onChange: (key: string, value: unknown) => void;
	gridClassName?: string;
	fieldClassName?: string;
	inputWrapClassName?: string;
	getFieldClassName?: (field: BookingFormField) => string | undefined;
};

const DynamicFieldsGrid = ({
	fields,
	values,
	onChange,
	gridClassName,
	fieldClassName,
	inputWrapClassName,
	getFieldClassName,
}: Props) => {
	const classes = ['booking-edit__registration-fields', gridClassName]
		.filter(Boolean)
		.join(' ');

	return (
		<div className={classes}>
			{fields.map((field) => {
				if (!isFieldVisible(field.visibilityRule, values)) {
					return null;
				}

				const fieldDefinition = getBookingFieldDefinition(field);

				return (
					<div
						key={field.name}
						className={[
							'booking-edit__registration-field',
							`booking-edit__registration-field--width-${field.width ?? 6}`,
							fieldClassName,
							getFieldClassName?.(field),
						]
							.filter(Boolean)
							.join(' ')}
					>
						{field.description && (
							<p className="booking-edit__field-description">
								{field.description}
							</p>
						)}
						<div className={inputWrapClassName}>
							<InputField
								{...fieldDefinition}
								status="LOADED"
								formTouched={false}
								disabled={false}
								value={getBookingFieldValue(field, values)}
								onChange={(value) => onChange(field.name, value)}
							/>
						</div>
					</div>
				);
			})}
		</div>
	);
};

export default DynamicFieldsGrid;
