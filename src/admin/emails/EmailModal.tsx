import apiFetch from '@wordpress/api-fetch';
import { Modal, Spinner } from '@wordpress/components';
import { lazy, Suspense, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import type { MailTemplate } from '../../types/types';

const EmailTemplateEditor = lazy(() => import('@events/emails/EmailTemplateEditor'));

type Props = {
	template: MailTemplate | null;
	onSaved: (template: MailTemplate) => void;
	onReset: (template: MailTemplate) => void;
	onClose: () => void;
};

const EmailModal = ({ template, onSaved, onReset, onClose }: Props) => {
	const [form, setForm] = useState<MailTemplate | null>(template);
	const [saving, setSaving] = useState(false);
	const [error, setError] = useState<string | null>(null);

	useEffect(() => {
		setForm(template);
		setError(null);
		setSaving(false);
	}, [template]);

	if (!template) {
		return null;
	}

	if (!form) {
		return null;
	}

	const onSave = () => {
		setSaving(true);
		setError(null);
		apiFetch<MailTemplate>({
			path: `/events/v3/emails/${template.key}`,
			method: 'PUT',
			data: {
				enabled: form.enabled,
				subject: form.subject ?? '',
				body: form.body,
				replyTo: form.replyTo ?? '',
			},
		})
			.then((saved) => {
				onSaved(saved);
				setForm(saved);
				setSaving(false);
			})
			.catch((err: { message?: string }) => {
				setError(err?.message ?? __('Save failed.', 'ctx-events'));
				setSaving(false);
			});
	};

	const handleReset = () => {
		setSaving(true);
		setError(null);
		apiFetch<MailTemplate>({
			path: `/events/v3/emails/${template.key}`,
			method: 'DELETE',
		})
			.then((saved) => {
				onReset(saved);
				setForm(saved);
				setSaving(false);
			})
			.catch((err: { message?: string }) => {
				setError(err?.message ?? __('Reset failed.', 'ctx-events'));
				setSaving(false);
			});
	};

	return (
		<Modal onRequestClose={onClose} title={template.label} size="large">
			<Suspense fallback={<Spinner />}>
				<EmailTemplateEditor
					template={form}
					onChange={setForm}
					onSave={onSave}
					onReset={handleReset}
					onClose={onClose}
					saving={saving}
					error={error}
					showEnabledToggle={false}
				/>
			</Suspense>
		</Modal>
	);
};

export default EmailModal;
