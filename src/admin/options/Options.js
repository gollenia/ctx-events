// options/index.js

import apiFetch from '@wordpress/api-fetch';
import {
	Button,
	Notice,
	SelectControl,
	Spinner,
	TabPanel,
	TextareaControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const groupBy = (items, keyFn) => {
	return items.reduce((acc, item) => {
		const key = keyFn(item);
		if (!acc[key]) {
			acc[key] = [];
		}
		acc[key].push(item);
		return acc;
	}, {});
};

const domainLabel = (domain) => {
	switch (domain) {
		case 'events':
			return __('Events', 'ctx-events');
		case 'booking':
			return __('Bookings', 'ctx-events');
		case 'payment':
			return __('Payments', 'ctx-events');
		case 'emails':
			return __('E-Mails', 'ctx-events');
		case 'advanced':
			return __('Advanced', 'ctx-events');
		default:
			return domain || __('General', 'ctx-events');
	}
};

const renderFieldControl = (field, onChange) => {
	const { type, label, description, value } = field;

	switch (type) {
		case 'bool':
		case 'boolean': {
			return (
				<ToggleControl
					label={label}
					help={description}
					checked={!!value}
					onChange={(checked) => onChange(checked)}
				/>
			);
		}

		case 'int':
		case 'integer':
		case 'number': {
			return (
				<TextControl
					type="number"
					label={label}
					help={description}
					value={value ?? ''}
					onChange={(val) => onChange(val === '' ? null : Number(val))}
				/>
			);
		}

		case 'text': {
			return (
				<TextareaControl
					label={label}
					help={description}
					value={value ?? ''}
					onChange={(val) => onChange(val)}
				/>
			);
		}

		case 'select': {
			const options = field.options || [];
			return (
				<SelectControl
					label={label}
					help={description}
					value={value ?? ''}
					onChange={(val) => onChange(val)}
					options={options.map((opt) =>
						typeof opt === 'string'
							? { label: opt, value: opt }
							: { label: opt.label, value: opt.value },
					)}
				/>
			);
		}

		default: {
			return (
				<TextControl
					label={label}
					help={description}
					value={value ?? ''}
					onChange={(val) => onChange(val)}
				/>
			);
		}
	}
};

const Options = () => {
	const [fields, setFields] = useState([]);

	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);
	const [notice, setNotice] = useState(null);
	const [error, setError] = useState(null);

	useEffect(() => {
		setLoading(true);
		setError(null);

		apiFetch({ path: '/events/v3/options' })
			.then((response) => {
				setFields(response || []);
			})
			.catch((err) => {
				setError(__('Fehler beim Laden der Einstellungen.', 'ctx-events'));
			})
			.finally(() => setLoading(false));
	}, []);

	const domains = useMemo(() => {
		const names = new Set(fields.map((f) => f.domain || 'general'));
		return Array.from(names);
	}, [fields]);

	const tabs = domains.map((domain) => ({
		name: domain,
		title: domainLabel(domain),
		className: `ctx-events-settings-tab-${domain}`,
	}));

	const handleFieldChange = (key, newValue) => {
		setFields((prev) =>
			prev.map((field) =>
				field.key === key ? { ...field, value: newValue } : field,
			),
		);
	};

	const handleSave = () => {
		setSaving(true);
		setNotice(null);
		setError(null);

		const values = fields.reduce((acc, field) => {
			acc[field.key] = field.value;
			return acc;
		}, {});

		apiFetch({
			path: '/events/v3/options',
			method: 'POST',
			data: { values },
		})
			.then(() => {
				setNotice(__('Einstellungen wurden gespeichert.', 'ctx-events'));
			})
			.catch((err) => {
				setError(__('Fehler beim Speichern der Einstellungen.', 'ctx-events'));
			})
			.finally(() => setSaving(false));
	};

	if (loading) {
		return (
			<div className="ctx-events-settings-loading">
				<Spinner />
			</div>
		);
	}

	return (
		<div className="ctx-events-settings">
			<h1>{__('Events Einstellungen', 'ctx-events')}</h1>

			{error && (
				<Notice status="error" isDismissible onRemove={() => setError(null)}>
					{error}
				</Notice>
			)}

			{notice && (
				<Notice status="success" isDismissible onRemove={() => setNotice(null)}>
					{notice}
				</Notice>
			)}

			<TabPanel className="ctx-events-settings-tabs" tabs={tabs}>
				{(tab) => {
					const domain = tab.name || 'general';

					const domainFields = fields
						.filter((f) => (f.domain || 'general') === domain)
						.sort((a, b) => (a.order ?? 0) - (b.order ?? 0));

					// Sections (Cards) gruppieren
					const sections = groupBy(domainFields, (f) => f.section || 'general');

					return (
						<div className="ctx-events-settings-tab-panel">
							{Object.entries(sections).map(([sectionId, sectionFields]) => {
								const sectionLabel = sectionFields[0].section_label || null;

								return (
									<div key={sectionId} className="ctx-events-settings-section">
										{sectionLabel && <h2>{sectionLabel}</h2>}
										<div>
											{sectionFields.map((field) => (
												<div
													key={field.key}
													className="ctx-events-settings-field"
												>
													{renderFieldControl(field, (newValue) =>
														handleFieldChange(field.key, newValue),
													)}
												</div>
											))}
										</div>
									</div>
								);
							})}
						</div>
					);
				}}
			</TabPanel>

			<div className="ctx-events-settings-actions">
				<Button
					isBusy={saving}
					onClick={handleSave}
					style={{ marginTop: '20px' }}
					variant="primary"
				>
					{__('Änderungen speichern', 'ctx-events')}
				</Button>
			</div>
		</div>
	);
};

export default Options;
