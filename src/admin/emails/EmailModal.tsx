import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Flex,
	Modal,
	Notice,
	TextareaControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

type MailTemplate = {
	key: string;
	label: string;
	description: string;
	trigger: string;
	target: string;
	source: string;
	isCustomized: boolean;
	enabled: boolean;
	subject: string | null;
	body: string;
	replyTo: string | null;
};

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
			<div className="ctx-email-modal">
				<p className="ctx-email-modal__description">{form.description}</p>
				<Notice status="info" isDismissible={false}>
					{__(
						'This modal is the placeholder for the future Tiptap/Lexical editor integration.',
						'ctx-events',
					)}
				</Notice>
				{error ? (
					<Notice status="error" isDismissible={false}>
						{error}
					</Notice>
				) : null}

				<ToggleControl
					label={__('Enabled', 'ctx-events')}
					checked={form.enabled}
					onChange={(enabled) =>
						setForm((current) => (current ? { ...current, enabled } : current))
					}
				/>

				<TextControl
					label={__('Reply-To', 'ctx-events')}
					value={form.replyTo ?? ''}
					onChange={(replyTo) =>
						setForm((current) =>
							current ? { ...current, replyTo: replyTo || null } : current,
						)
					}
				/>

				<TextControl
					label={__('Subject', 'ctx-events')}
					value={form.subject ?? ''}
					onChange={(subject) =>
						setForm((current) =>
							current ? { ...current, subject: subject || null } : current,
						)
					}
				/>

				<TextareaControl
					label={__('Body', 'ctx-events')}
					value={form.body}
					rows={12}
					onChange={(body) =>
						setForm((current) => (current ? { ...current, body } : current))
					}
				/>

				<div className="ctx-email-modal__actions">
					<Flex justify="space-between">
						<Button variant="tertiary" onClick={handleReset} disabled={saving}>
							{__('Reset to preset', 'ctx-events')}
						</Button>
						<Flex gap={2}>
							<Button variant="secondary" onClick={onClose} disabled={saving}>
								{__('Close', 'ctx-events')}
							</Button>
							<Button
								variant="primary"
								onClick={onSave}
								isBusy={saving}
								disabled={saving}
							>
								{__('Save changes', 'ctx-events')}
							</Button>
						</Flex>
					</Flex>
				</div>
			</div>
		</Modal>
	);
};

export default EmailModal;
