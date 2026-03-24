import apiFetch from '@wordpress/api-fetch';
import { Notice, PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { lazy, Suspense, useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	applyMailTemplateOverrides,
	createMailTemplateOverride,
} from '@events/emails';

import type { BookingSidebarProps } from './types';
import type { MailTemplate } from '../../../../types/types';

const EmailTemplateEditor = lazy(() => import('@events/emails/EmailTemplateEditor'));

const MailSettings = ({ meta, updateMeta }: BookingSidebarProps) => {
	const [baseTemplates, setBaseTemplates] = useState<MailTemplate[]>([]);
	const [activeKey, setActiveKey] = useState<string>('');
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState<string | null>(null);
	const [form, setForm] = useState<MailTemplate | null>(null);
	const overrides = meta._booking_mails ?? [];

	useEffect(() => {
		apiFetch({ path: '/events/v3/emails' })
			.then((data: MailTemplate[]) => {
				setBaseTemplates(data);
				setActiveKey(data[0]?.key ?? '');
				setLoading(false);
			})
			.catch((err: { message?: string }) => {
				setError(err?.message ?? __('Unknown error', 'ctx-events'));
				setLoading(false);
			});
	}, []);

	const templates = useMemo(
		() => applyMailTemplateOverrides(baseTemplates, overrides),
		[baseTemplates, overrides],
	);

	useEffect(() => {
		const template = templates.find((item) => item.key === activeKey) ?? null;
		setForm(template);
	}, [activeKey, templates]);

	const options = useMemo(
		() =>
			templates.map((template) => ({
				label: template.label,
				value: template.key,
			})),
		[templates],
	);

	const updateOverride = (template: MailTemplate) => {
		const nextOverride = createMailTemplateOverride(template);
		const currentOverrides = meta._booking_mails ?? [];
		const otherOverrides = currentOverrides.filter((item) => item.key !== template.key);

		updateMeta({
			_booking_mails: [...otherOverrides, nextOverride],
		});
	};

	const handleSave = () => {
		if (!form) {
			return;
		}

		setError(null);
		updateOverride(form);
	};

	const handleReset = () => {
		if (!form) {
			return;
		}

		setError(null);
		updateMeta({
			_booking_mails: overrides.filter((item) => item.key !== form.key),
		});
	};

	return (
		<PanelBody
			title={__('Mail Settings', 'ctx-events')}
			initialOpen={true}
			className="events-booking-settings"
		>
			{loading ? <Spinner /> : null}
			{!loading && error && !form ? (
				<Notice status="error" isDismissible={false}>
					{error}
				</Notice>
			) : null}
			{!loading && !error && !templates.length ? (
				<Notice status="info" isDismissible={false}>
					{__('No email templates found.', 'ctx-events')}
				</Notice>
			) : null}
			{options.length ? (
				<SelectControl
					label={__('Template', 'ctx-events')}
					value={activeKey}
					options={options}
					onChange={setActiveKey}
				/>
			) : null}
			{form ? (
				<Notice status="info" isDismissible={false}>
					{__(
						'Changes here are saved as event-specific mail overrides and apply only to this event after the post is updated.',
						'ctx-events',
					)}
				</Notice>
			) : null}
			{form ? (
				<Suspense fallback={<Spinner />}>
					<EmailTemplateEditor
						template={form}
						onChange={setForm}
						onSave={handleSave}
						onReset={handleReset}
						error={error}
						saveLabel={__('Use for this event', 'ctx-events')}
						resetLabel={__('Use global template', 'ctx-events')}
					/>
				</Suspense>
			) : null}
		</PanelBody>
	);
};

export default MailSettings;
