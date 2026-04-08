import DataTable from '@events/datatable/DataTable';
import type {
	DataFieldConfig,
	DataTableAction,
	DataViewConfig,
} from '@events/datatable/types';
import apiFetch from '@wordpress/api-fetch';
import { Spinner } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { MailTemplate } from '../../types/types';
import EmailModal from './EmailModal';

const targetLabels: Record<string, string> = {
	customer: __('Customer', 'ctx-events'),
	admin: __('Admin', 'ctx-events'),
	billing_contact: __('Billing contact', 'ctx-events'),
	event_contact: __('Event contact', 'ctx-events'),
};

const sourceLabels: Record<string, string> = {
	preset: __('Preset', 'ctx-events'),
	database: __('Customized', 'ctx-events'),
};

const updateTemplateEnabled = async (
	template: MailTemplate,
	enabled: boolean,
): Promise<MailTemplate> =>
	apiFetch<MailTemplate>({
		path: `/events/v3/emails/${template.key}`,
		method: 'PUT',
		data: {
			enabled,
			subject: template.subject ?? '',
			body: template.body,
			replyTo: template.replyTo ?? '',
			recipientConfig: template.recipientConfig,
		},
	});

const EmailsTable = () => {
	const [emails, setEmails] = useState<MailTemplate[]>([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState<string | null>(null);
	const [activeKey, setActiveKey] = useState<string | null>(null);
	const [view, setView] = useState<DataViewConfig>({
		search: '',
		page: 1,
		perPage: 100,
		sort: { field: 'label', direction: 'asc' },
		filters: [{ field: 'target', operator: 'is', value: '' }],
		titleField: 'label',
		fields: ['label', 'target', 'description', 'source', 'enabled'],
	});

	useEffect(() => {
		apiFetch({ path: '/events/v3/emails' })
			.then((data: MailTemplate[]) => {
				setEmails(data);
				setLoading(false);
			})
			.catch((err: { message?: string }) => {
				setError(err?.message ?? __('Unknown error', 'ctx-events'));
				setLoading(false);
			});
	}, []);

	const filteredEmails = useMemo(() => {
		const search = (view.search ?? '').trim().toLowerCase();
		const targetFilter = String(
			view.filters.find((filter) => filter.field === 'target')?.value ?? '',
		);

		return emails.filter((item) => {
			if (targetFilter !== '' && item.target !== targetFilter) {
				return false;
			}

			if (search === '') {
				return true;
			}

			const haystack = [
				item.label,
				item.description,
				item.trigger,
				item.target,
				item.subject ?? '',
				item.body,
			]
				.join('\n')
				.toLowerCase();

			return haystack.includes(search);
		});
	}, [emails, view.filters, view.search]);

	const fields = useMemo<Array<DataFieldConfig>>(
		() => [
			{
				id: 'label',
				label: __('Email', 'ctx-events'),
				enableSorting: true,
				className: 'column-title column-primary has-row-actions',
				render: (item: MailTemplate) => (
					<div className="ctx-email-cell">
						<strong>
							<a
								href="#"
								onClick={(event) => {
									event.preventDefault();
									setActiveKey(item.key);
								}}
							>
								{item.label}
							</a>
						</strong>
					</div>
				),
			},
			{
				id: 'target',
				label: __('Recipient', 'ctx-events'),
				enableSorting: true,
				filterBy: {
					type: 'text',
					label: __('All recipients', 'ctx-events'),
					elements: [
						{ value: 'customer', label: __('Customer', 'ctx-events') },
						{ value: 'admin', label: __('Admin', 'ctx-events') },
					],
				},
				render: (item: MailTemplate) => (
					<span>{targetLabels[item.target] ?? item.target}</span>
				),
			},
			{
				id: 'description',
				label: __('Description', 'ctx-events'),
				enableSorting: false,
				render: (item: MailTemplate) => (
					<span className="ctx-email-description">{item.description}</span>
				),
			},
			{
				id: 'trigger',
				label: __('Trigger', 'ctx-events'),
				enableSorting: true,
			},
			{
				id: 'source',
				label: __('Source', 'ctx-events'),
				enableSorting: true,
				render: (item: MailTemplate) => (
					<span className={`ctx-email-badge ctx-email-badge--${item.source}`}>
						{sourceLabels[item.source] ?? item.source}
					</span>
				),
			},
			{
				id: 'enabled',
				label: __('Enabled', 'ctx-events'),
				enableSorting: true,
				isPluginStatus: true,
				render: (item: MailTemplate) =>
					item.enabled ? __('Yes', 'ctx-events') : __('No', 'ctx-events'),
			},
		],
		[],
	);

	const actions = useMemo<Array<DataTableAction>>(
		() => [
			{
				id: 'edit',
				label: __('Edit', 'ctx-events'),
				callback: (items) => setActiveKey(items[0]?.key ?? null),
			},
			{
				id: 'toggle-enabled',
				delete: (item: MailTemplate) => !item.enabled,
				label: (item: MailTemplate) =>
					item.enabled
						? __('Disable', 'ctx-events')
						: __('Enable', 'ctx-events'),
				callback: (items) => {
					const template = items[0] as MailTemplate | undefined;

					if (!template) {
						return;
					}

					updateTemplateEnabled(template, !template.enabled).then((updated) => {
						setEmails((current) =>
							current.map((item) =>
								item.key === updated.key ? updated : item,
							),
						);
					});
				},
			},
		],
		[],
	);

	const activeTemplate =
		activeKey === null
			? null
			: (emails.find((item) => item.key === activeKey) ?? null);

	if (loading) {
		return (
			<div className="ctx-email-admin__loading">
				<Spinner />
			</div>
		);
	}

	if (error) {
		return <div>{error}</div>;
	}

	return (
		<>
			<DataTable<MailTemplate>
				data={filteredEmails}
				title={__('Emails', 'ctx-events')}
				fields={fields}
				view={view}
				variant="plugins"
				actions={actions}
				search={true}
				onChangeView={(updates) => setView((prev) => ({ ...prev, ...updates }))}
				isLoading={loading}
				searchLabel={__('Search emails…', 'ctx-events')}
				empty={() => <div>{__('No email templates found.', 'ctx-events')}</div>}
			>
				<DataTable.Header />
				<DataTable.Filter />
				<DataTable.Table />
				<DataTable.Pagination />
			</DataTable>

			<EmailModal
				template={activeTemplate}
				onSaved={(template) => {
					setEmails((current) =>
						current.map((item) =>
							item.key === template.key ? template : item,
						),
					);
					setActiveKey(template.key);
				}}
				onReset={(template) => {
					setEmails((current) =>
						current.map((item) =>
							item.key === template.key ? template : item,
						),
					);
					setActiveKey(template.key);
				}}
				onClose={() => setActiveKey(null)}
			/>
		</>
	);
};

export default EmailsTable;
