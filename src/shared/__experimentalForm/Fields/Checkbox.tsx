import type { ChangeEvent, InvalidEvent } from 'react';
import { useRef, useState } from '@wordpress/element';
import type { FieldValue } from '../types';
import { sanitizeInlineHtml } from '../sanitize';

type Props = {
	label: string;
	name: string;
	width: number;
	disabled: boolean;
	required: boolean;
	defaultChecked: boolean;
	type: 'checkbox' | 'toggle';
	customErrorMessage?: string;
	error?: string;
	value: boolean;
	help?: string;
	toggle?: boolean;
	formTouched: boolean;
	onChange: (value: FieldValue) => void;
};

const Checkbox = (props: Props) => {
	const { label, name, width = 6, onChange, type, value, help = '', toggle = false, customErrorMessage, error } = props;

	const inputRef = useRef<HTMLInputElement>(null);
	const [touched, setTouched] = useState(false);

	const onChangeHandler = (event: ChangeEvent<HTMLInputElement>) => {
		onChange(event.target.checked);
	};

	const setInvalidity = (event: InvalidEvent<HTMLInputElement>) => {
		if (!props.customErrorMessage) return;
		event.target.setCustomValidity(props.customErrorMessage);
	};

	const isTouched = props.formTouched || touched;
	const hasError = !!error || (!inputRef?.current?.validity.valid && isTouched);
	const errorMessage = error ?? customErrorMessage ?? inputRef.current?.validationMessage;
	const errorId = `${name}-error`;
	const labelText = help || label;

	const classes = [
		'ctx-form-field',
		toggle ? 'toggle' : 'checkbox',
		hasError ? 'error' : '',
	].join(' ');

	return (
		<div className={classes} style={{ gridColumn: `span ${width}` }}>
			<label>
				<div className="toggle__control">
					<input
						id={name}
						name={name}
						disabled={props.disabled}
						required={props.required}
						aria-required={props.required}
						aria-invalid={hasError || undefined}
						aria-describedby={hasError && errorMessage ? errorId : undefined}
						ref={inputRef}
						onClick={() => setTouched(true)}
						checked={value}
						type="checkbox"
						onChange={onChangeHandler}
						onInvalid={setInvalidity}
					/>
					{toggle && <span className="toggle__switch" aria-hidden="true" />}
				</div>
				<span dangerouslySetInnerHTML={{ __html: sanitizeInlineHtml(labelText) }} />
			</label>
			{hasError && errorMessage && (
				<span id={errorId} role="alert" className="error-message">
					{errorMessage}
				</span>
			)}
		</div>
	);
};

Checkbox.defaultProps = {
	label: '',
	help: '',
	width: 6,
	disabled: false,
	required: false,
	defaultChecked: false,
	type: 'checkbox',
};

export default Checkbox;
export type { Props as CheckboxProps };
