import PanelTitle from '@events/adminfields/PanelTitle';
import DataTable from '@events/datatable/DataTable';
import type {
	DataFieldConfig,
	DataTableAction,
	DataViewConfig,
} from '@events/datatable/types';
import {
	applyMailTemplateOverrides,
	createMailTemplateOverride,
} from '@events/emails';
import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Flex,
	FlexBlock,
	Modal,
	Notice,
	PanelBody,
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

type MailSettingsView = 'list' | 'editor';
type MailSettingsTableRow = MailTemplate & {
	id: string;
	hasOverride: boolean;
};

const targetLabels: Record<string, string> = {
	customer: __('Customer', 'ctx-events'),
	admin: __('Admin', 'ctx-events'),
	billing_contact: __('Billing contact', 'ctx-events'),
	event_contact: __('Event contact', 'ctx-events'),
};

const MailSettings = ({ meta, updateMeta }: BookingSidebarProps) => {
	const [isOpen, setIsOpen] = useState(false);
	const [baseTemplates, setBaseTemplates] = useState<MailTemplate[]>([]);
	const [activeKey, setActiveKey] = useState<string>('');
	const [view, setView] = useState<MailSettingsView>('list');
	const [tableView, setTableView] = useState<DataViewConfig>({
		search: '',
		page: 1,
		perPage: 100,
		sort: { field: 'label', direction: 'asc' },
		filters: [],
		titleField: 'label',
		fields: ['label', 'target', 'trigger', 'scope'],
	});
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

	const tableRows = useMemo<MailSettingsTableRow[]>(
		() =>
			templates.map((template) => ({
				...template,
				id: template.key,
				hasOverride: overrides.some((item) => item.key === template.key),
			})),
		[templates, overrides],
	);

	useEffect(() => {
		const template = templates.find((item) => item.key === activeKey) ?? null;
		setForm(template);
	}, [activeKey, templates]);

	const filteredRows = useMemo(() => {
		const search = (tableView.search ?? '').trim().toLowerCase();
		const items = [...tableRows];
		const { field, direction } = tableView.sort;

		if (search !== '') {
			items.splice(
				0,
				items.length,
				...items.filter((item) =>
					[
						item.label,
						item.description,
						item.subject ?? '',
						targetLabels[item.target] ?? item.target,
						item.trigger,
						item.hasOverride
							? __('Event override', 'ctx-events')
							: __('Global template', 'ctx-events'),
					]
						.join('\n')
						.toLowerCase()
						.includes(search),
				),
			);
		}

		items.sort((left, right) => {
			const leftValue =
				field === 'scope'
					? left.hasOverride
						? '1'
						: '0'
					: String((left as Record<string, unknown>)[field] ?? '');
			const rightValue =
				field === 'scope'
					? right.hasOverride
						? '1'
						: '0'
					: String((right as Record<string, unknown>)[field] ?? '');

			const comparison = leftValue.localeCompare(rightValue, undefined, {
				sensitivity: 'base',
			});

			return direction === 'asc' ? comparison : comparison * -1;
		});

		return items;
	}, [tableRows, tableView]);

	const tableFields = useMemo<Array<DataFieldConfig>>(
		() => [
			{
				id: 'label',
				label: __('Mail', 'ctx-events'),
				enableSorting: true,
				className:
					'column-title column-primary has-row-actions events-booking-mail-table__title',
				render: (item: MailSettingsTableRow) => (
					<Flex
						direction="column"
						gap={0}
						style={{ alignItems: 'flex-start' }}
					>
						<strong style={{ fontSize: '13px' }}>{item.label}</strong>
					</Flex>
				),
			},
			{
				id: 'target',
				label: __('Recipient', 'ctx-events'),
				enableSorting: true,
				render: (item: MailSettingsTableRow) => (
					<span>{targetLabels[item.target] ?? item.target}</span>
				),
			},
			{
				id: 'trigger',
				label: __('Trigger', 'ctx-events'),
				enableSorting: true,
				render: (item: MailSettingsTableRow) => (
					<code className="events-booking-mail-table__trigger">
						{item.trigger}
					</code>
				),
			},
			{
				id: 'scope',
				label: __('Scope', 'ctx-events'),
				enableSorting: true,
				render: (item: MailSettingsTableRow) => (
					<span
						className={
							item.hasOverride
								? 'events-booking-mail-table__badge'
								: 'events-booking-mail-table__badge events-booking-mail-table__badge--muted'
						}
					>
						{item.hasOverride
							? __('Event override', 'ctx-events')
							: __('Global template', 'ctx-events')}
					</span>
				),
			},
		],
		[],
	);

	const tableActions = useMemo<Array<DataTableAction>>(
		() => [
			{
				id: 'edit',
				label: __('Edit', 'ctx-events'),
				isPrimary: true,
				callback: (items) => {
					const item = items[0] as MailSettingsTableRow | undefined;
					if (!item) {
						return;
					}

					openTemplate(item.key);
				},
			},
		],
		[],
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

	const openTemplate = (templateKey: string) => {
		setActiveKey(templateKey);
		setError(null);
		setView('editor');
	};

	const closeModal = () => {
		setIsOpen(false);
		setView('list');
		setError(null);
	};

	const activeOverride = activeKey
		? (overrides.find((item) => item.key === activeKey) ?? null)
		: null;

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
					onRequestClose={closeModal}
					isFullScreen={true}
					className="events-booking-mail-settings-modal-shell"
				>
					<Flex direction="column" gap={4}>
						{loading ? <Spinner /> : null}
						{!loading && error && view === 'list' ? (
							<Notice status="error" isDismissible={false}>
								{error}
							</Notice>
						) : null}
						{!loading && !error && !templates.length ? (
							<Notice status="info" isDismissible={false}>
								{__('No email templates found.', 'ctx-events')}
							</Notice>
						) : null}
						{!loading && templates.length && view === 'list' ? (
							<div className="events-booking-mail-settings-list">
								<DataTable<MailSettingsTableRow>
									data={filteredRows}
									fields={tableFields}
									view={tableView}
									onChangeView={(updates) =>
										setTableView((prev) => ({ ...prev, ...updates }))
									}
									isLoading={loading}
									actions={tableActions}
									search={true}
									searchLabel={__('Search emails…', 'ctx-events')}
									availableStatusItems={[]}
									screenMeta={false}
									variant="plugins"
									empty={() => (
										<div>{__('No email templates found.', 'ctx-events')}</div>
									)}
								>
									<DataTable.Table />
								</DataTable>
							</div>
						) : null}
						{view === 'editor' && form ? (
							<Flex direction="column" gap={4}>
								<Flex
									align="flex-start"
									gap={4}
									style={{ flexWrap: 'wrap' }}
								>
									<Button variant="tertiary" onClick={() => setView('list')}>
										{__('Back', 'ctx-events')}
									</Button>
									<FlexBlock>
										<h2 style={{ margin: 0, fontSize: '20px', lineHeight: 1.2 }}>
											{form.label}
										</h2>
										<p style={{ margin: '0.25rem 0 0', color: '#4b5563' }}>
											{form.description}
										</p>
									</FlexBlock>
								</Flex>
								{activeOverride ? (
									<Notice status="warning" isDismissible={false}>
										{__(
											'This event currently overrides the global template.',
											'ctx-events',
										)}
									</Notice>
								) : null}
							</Flex>
						) : null}
						{view === 'editor' && form ? (
							<Notice status="info" isDismissible={false}>
								{__(
									'Changes here are saved as event-specific mail overrides and apply only to this event after the post is updated.',
									'ctx-events',
								)}
							</Notice>
						) : null}
						{view === 'editor' && form ? (
							<Suspense fallback={<Spinner />}>
								<EmailTemplateEditor
									template={form}
									onChange={setForm}
									onSave={handleSave}
									onReset={handleReset}
									onClose={closeModal}
									error={error}
									saveLabel={__('Use for this event', 'ctx-events')}
									resetLabel={__('Use global template', 'ctx-events')}
								/>
							</Suspense>
						) : null}
						<Flex justify="flex-end">
							<Button variant="secondary" onClick={closeModal}>
								{__('Close', 'ctx-events')}
							</Button>
						</Flex>
					</Flex>
				</Modal>
			) : null}
		</>
	);
};

export default MailSettings;
