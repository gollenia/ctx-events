import type { FormEvent, ChangeEvent } from 'react';
import { useEffect, useRef, useState } from '@wordpress/element';
import {
	getDefaultFormValues,
	isFieldVisible,
	type FormFieldDefinition,
	type FormResponse,
	type FormState,
	type FormValues,
} from '../form-core';
import { FormFields } from './FormFields';
import { sanitizeHtml } from './sanitize';

type FormProps = {
	data?: FormFieldDefinition[];
	formUrl?: string;
	onSubmit?: (event: FormEvent<HTMLFormElement>, data: FormValues) => void;
	onSubmissionFinished?: (response: FormResponse) => void;
	validate?: boolean;
	submitUrl?: string;
	extraData?: FormValues;
	onStateChange?: (state: FormState) => void;
	onChange?: (form: FormValues) => void;
};

const Form = (props: FormProps) => {
	const {
		data = [],
		formUrl = '',
		onSubmit,
		submitUrl = '',
		onChange,
		onStateChange,
		extraData,
		onSubmissionFinished,
	} = props;

	const [status, setStatus] = useState<FormState>('LOADING');
	const [fields, setFields] = useState<FormFieldDefinition[]>([]);
	const [form, setForm] = useState<FormValues>({});
	const [response, setResponse] = useState<FormResponse>();
	const [touched, setTouched] = useState(false);

	useEffect(() => {
		onStateChange?.(status);
	}, [status]);

	const formRef = useRef<HTMLFormElement>(null);

	useEffect(() => {
		if (!formUrl && data) {
			setFields(data);
			setStatus('LOADED');
			return;
		}
		fetch(formUrl)
			.then((res) => res.json())
			.then((json: { fields: FormFieldDefinition[] }) => {
				setFields(json.fields);
				setStatus('LOADED');
				setForm(getDefaultFormValues(json.fields));
			});
	}, [data, formUrl]);

	if (fields.length === 0) return null;

	const visibleFields = fields.filter((field) => isFieldVisible(field.visibilityRule, form));

	const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
		const visibleValues = Object.fromEntries(
			visibleFields.map((field) => [field.name, form[field.name] ?? field.defaultValue ?? '']),
		);
		const formData: FormValues = { ...visibleValues, ...extraData };

		event.preventDefault();
		if (onSubmit) {
			onSubmit(event, formData);
			return;
		}

		if (!formRef.current?.checkValidity()) {
			formRef.current?.reportValidity();
			return;
		}

		if (!submitUrl) {
			console.error('wp-react-form', 'No URL or onSubmit callback provided');
			return;
		}

		if (status === 'SUBMITTING' || status === 'SUCCESS') return;

		setStatus('SUBMITTING');
		fetch(submitUrl, {
			method: 'POST',
			body: JSON.stringify(formData),
			headers: {
				'Content-Type': 'application/json',
			},
		})
			.then((res) => res.json())
			.then((data: FormResponse) => {
				setStatus(data.success ? 'SUCCESS' : 'ERROR');
				setResponse(data);
				onSubmissionFinished?.(data);
			});
	};

	const resetForm = () => {
		setStatus('LOADED');
		setForm(getDefaultFormValues(fields));
	};

	if (status === 'LOADING') return null;

	const onFormChange = (_event: ChangeEvent<HTMLFormElement>) => {
		onChange?.(form);
	};

	const classes = [
		'ctx-form',
		status === 'LOADED' ? 'ctx-form--loaded' : '',
		status === 'ERROR' ? 'ctx-form--error' : '',
		status === 'SUBMITTING' ? 'ctx-form--submitting' : '',
		status === 'SUCCESS' ? 'ctx-form--submitted' : '',
	].join(' ');

	return (
		<>
			<form
				className={classes}
				ref={formRef}
				onSubmit={handleSubmit}
				onInvalid={() => setTouched(true)}
				onChange={onFormChange}
				onReset={resetForm}
			>
				{status === 'ERROR' && (
					<div
						role="alert"
						className="ctx-form__response ctx-form__response--error"
						dangerouslySetInnerHTML={{ __html: sanitizeHtml(response?.message?.html ?? '') }}
					/>
				)}

				<div
					role="status"
					aria-live="polite"
					style={{
						opacity: status === 'SUCCESS' ? 1 : 0,
						pointerEvents: status === 'SUCCESS' ? 'all' : 'none',
						transition: 'all 0.3s',
						left: 0,
						top: 0,
						bottom: 0,
						right: 0,
						position: 'absolute',
						zIndex: 5,
					}}
					className={`ctx-form__response ${status === 'SUCCESS' ? 'ctx-form__response--show' : ''}`}
					dangerouslySetInnerHTML={{
						__html: sanitizeHtml(response?.message?.html ?? '<p>Success</p>'),
					}}
				/>

				<FormFields
					fields={visibleFields}
					formData={form}
					status={status}
					disabled={status === 'SUBMITTING'}
					formTouched={touched}
					onChange={(name, value) => {
						setForm((prev) => ({ ...prev, [name]: value }));
					}}
				/>
			</form>
		</>
	);
};

export default Form;
