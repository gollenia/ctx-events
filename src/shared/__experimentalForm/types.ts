export type InputType =
	| 'text'
	| 'email'
	| 'url'
	| 'color'
	| 'tel'
	| 'password'
	| 'search'
	| 'date'
	| 'week'
	| 'month'
	| 'time'
	| 'datetime-local'
	| 'year'
	| 'number'
	| 'range'
	| 'numberpicker'
	| 'select'
	| 'combobox'
	| 'radio'
	| 'options'
	| 'textarea'
	| 'checkbox'
	| 'toggle'
	| 'country'
	| 'html'
	| 'hidden'
	| 'submit'
	| 'file';

export type FieldValue = string | boolean | number;

export type FormValues = Record<string, FieldValue>;

export type FormState = 'LOADING' | 'LOADED' | 'SUBMITTING' | 'SUCCESS' | 'ERROR';

export type CountryRegion =
	| 'world'
	| 'europe'
	| 'dach'
	| 'asia'
	| 'africa'
	| 'oceania'
	| 'north-america'
	| 'south-america'
	| 'americas';

export type SelectOptions = string[] | Record<string, string>;

export type FormResponse = {
	success: boolean;
	message: {
		html: string;
		attributes: {
			class: string;
		};
	};
};

export type VisibilityRule = {
	field: string;
	value: unknown;
	operator: 'equals' | 'not_equals' | 'not_empty';
};

export type FormFieldDefinition = {
	name: string;
	type: InputType;
	error?: string;
	defaultValue?: FieldValue;
	visibilityRule?: VisibilityRule | null;
	label?: string;
	width?: number;
	required?: boolean;
	placeholder?: string;
	options?: SelectOptions;
	min?: number;
	max?: number;
	rows?: number;
	pattern?: string | null;
	autoComplete?: string;
	help?: string;
	hint?: string;
	content?: string;
	region?: CountryRegion;
	hasTicks?: boolean;
	hasLabels?: boolean;
	alignment?: 'left' | 'center' | 'right';
	customError?: string;
	customErrorMessage?: string;
	hasEmptyOption?: boolean;
	multiple?: boolean;
	toggle?: boolean;
};

export type FieldRenderProps = FormFieldDefinition & {
	status: FormState;
	formTouched: boolean;
	disabled: boolean;
	value: FieldValue;
	onChange: (value: FieldValue) => void;
};
