import PanelTitle from '@events/adminfields/PanelTitle';
import {
	applyMailTemplateOverrides,
	createMailTemplateOverride,
} from '@events/emails';
import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Flex,
	FlexItem,
	Modal,
	Notice,
	PanelBody,
	SelectControl,
	Spinner,
} from '@wordpress/components';
import {
	lazy,
	Suspense,
	useEffect,
	useMemo,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { MailTemplate } from '../../../../types/types';
import icons from '../icons';
import type { BookingSidebarProps } from './types';

const EmailTemplateEditor = lazy(
	() => import('@events/emails/EmailTemplateEditor'),
);

const MailSettings = ({ meta, updateMeta }: BookingSidebarProps) => {
	const [isOpen, setIsOpen] = useState(false);
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
		const otherOverrides = currentOverrides.filter(
			(item) => item.key !== template.key,
		);

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
		<>
			<PanelBody
				title={
					<PanelTitle
						icon={icons.mail}
						title={__('Mail Settings', 'ctx-events')}
					/>
				}
				initialOpen={true}
				className="events-booking-settings"
			>
				<p className="events-booking-mail-settings__description">
					{__(
						'Edit event-specific email overrides in a separate dialog.',
						'ctx-events',
					)}
				</p>
				<Button variant="secondary" onClick={() => setIsOpen(true)}>
					{__('Open Mail Settings', 'ctx-events')}
				</Button>
			</PanelBody>

			{isOpen ? (
				<Modal
					title={__('Mail Settings', 'ctx-events')}
					onRequestClose={() => setIsOpen(false)}
					size="large"
				>
					<div className="events-booking-mail-settings-modal">
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
									onClose={() => setIsOpen(false)}
									error={error}
									saveLabel={__('Use for this event', 'ctx-events')}
									resetLabel={__('Use global template', 'ctx-events')}
								/>
							</Suspense>
						) : null}
						<Flex justify="flex-end" style={{ marginTop: '1rem' }}>
							<FlexItem>
								<Button variant="secondary" onClick={() => setIsOpen(false)}>
									{__('Close', 'ctx-events')}
								</Button>
							</FlexItem>
						</Flex>
					</div>
				</Modal>
			) : null}
		</>
	);
};

export default MailSettings;
