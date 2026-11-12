import { __ } from '@wordpress/i18n';

export type TiptapDocument = {
	type: 'doc';
	content: Array<Record<string, unknown>>;
};

export const EMAIL_TEMPLATE_TOKENS = [
	{ token: '{{booking.reference}}', label: __('Booking reference', 'ctx-events') },
	{ token: '{{booking.email}}', label: __('Booking email', 'ctx-events') },
	{ token: '{{booking.first_name}}', label: __('Booking first name', 'ctx-events') },
	{ token: '{{booking.last_name}}', label: __('Booking last name', 'ctx-events') },
	{ token: '{{booking.price}}', label: __('Booking price', 'ctx-events') },
	{
		token: '{{booking.cancellation_reason}}',
		label: __('Cancellation reason', 'ctx-events'),
	},
	{ token: '{{event.name}}', label: __('Event name', 'ctx-events') },
	{ token: '{{event.start}}', label: __('Event start', 'ctx-events') },
	{ token: '{{event.end}}', label: __('Event end', 'ctx-events') },
	{ token: '{{event.location}}', label: __('Event location', 'ctx-events') },
	{ token: '{{event.speaker}}', label: __('Event speaker', 'ctx-events') },
] as const;

export const EMAIL_TEMPLATE_BLOCKS = [
	{ type: 'registrationData', label: __('Registration data', 'ctx-events') },
	{ type: 'attendeeTable', label: __('Attendee table', 'ctx-events') },
	{ type: 'paymentInformation', label: __('Payment information', 'ctx-events') },
] as const;

export type EmailTemplateMentionItem =
	| {
			id: string;
			kind: 'token';
			label: string;
			searchText: string;
			token: (typeof EMAIL_TEMPLATE_TOKENS)[number]['token'];
	  }
	| {
			id: string;
			kind: 'block';
			label: string;
			searchText: string;
			type: (typeof EMAIL_TEMPLATE_BLOCKS)[number]['type'];
	  }
	| {
			id: string;
			kind: 'command';
			label: string;
			searchText: string;
			command:
				| 'bulletList'
				| 'orderedList'
				| 'heading1'
				| 'heading2'
				| 'heading3'
				| 'insertTable'
				| 'colorBoxInfo'
				| 'colorBoxWarning'
				| 'colorBoxSuccess';
	  };

export const EMAIL_TEMPLATE_TOKEN_MENTION_ITEMS: EmailTemplateMentionItem[] =
	EMAIL_TEMPLATE_TOKENS.map((token) => ({
		id: `token:${token.token}`,
		kind: 'token' as const,
		label: token.label,
		searchText: `${token.label} ${token.token}`.toLowerCase(),
		token: token.token,
	}));

export const EMAIL_TEMPLATE_BLOCK_MENTION_ITEMS: EmailTemplateMentionItem[] =
	[
		...EMAIL_TEMPLATE_BLOCKS.map((block) => ({
			id: `block:${block.type}`,
			kind: 'block' as const,
			label: block.label,
			searchText: `${block.label} ${block.type}`.toLowerCase(),
			type: block.type,
		})),
		{
			id: 'command:heading1',
			kind: 'command',
			label: __('Heading 1', 'ctx-events'),
			searchText: 'heading überschrift h1 title titel'.toLowerCase(),
			command: 'heading1' as const,
		},
		{
			id: 'command:heading2',
			kind: 'command',
			label: __('Heading 2', 'ctx-events'),
			searchText: 'heading überschrift h2 subtitle untertitel'.toLowerCase(),
			command: 'heading2' as const,
		},
		{
			id: 'command:heading3',
			kind: 'command',
			label: __('Heading 3', 'ctx-events'),
			searchText: 'heading überschrift h3'.toLowerCase(),
			command: 'heading3' as const,
		},
		{
			id: 'command:table',
			kind: 'command',
			label: __('Table', 'ctx-events'),
			searchText: 'table tabelle grid columns spalten rows zeilen'.toLowerCase(),
			command: 'insertTable' as const,
		},
		{
			id: 'command:color-box-info',
			kind: 'command',
			label: __('Info box', 'ctx-events'),
			searchText: 'info color box farbbox callout hint hinweis'.toLowerCase(),
			command: 'colorBoxInfo' as const,
		},
		{
			id: 'command:color-box-warning',
			kind: 'command',
			label: __('Warning box', 'ctx-events'),
			searchText: 'warning warnung color box farbbox callout alert'.toLowerCase(),
			command: 'colorBoxWarning' as const,
		},
		{
			id: 'command:color-box-success',
			kind: 'command',
			label: __('Success box', 'ctx-events'),
			searchText: 'success erfolg color box farbbox callout'.toLowerCase(),
			command: 'colorBoxSuccess' as const,
		},
		{
			id: 'command:bulletList',
			kind: 'command' as const,
			label: __('Bullets', 'ctx-events'),
			searchText: 'bullets bullet list unordered list'.toLowerCase(),
			command: 'bulletList' as const,
		},
		{
			id: 'command:orderedList',
			kind: 'command' as const,
			label: __('Numbers', 'ctx-events'),
			searchText: 'numbers numbered ordered list'.toLowerCase(),
			command: 'orderedList' as const,
		},
	];

const isRecord = (value: unknown): value is Record<string, unknown> =>
	typeof value === 'object' && value !== null;

export const isTiptapDocument = (value: string): boolean => {
	try {
		const parsed = JSON.parse(value) as unknown;
		return isRecord(parsed) && parsed.type === 'doc' && Array.isArray(parsed.content);
	} catch {
		return false;
	}
};

export const createTiptapDocumentFromText = (value: string): TiptapDocument => {
	const paragraphs = value.trim() === '' ? [''] : value.split(/\n{2,}/);

	return {
		type: 'doc',
		content: paragraphs.map((paragraph) => ({
			type: 'paragraph',
			content:
				paragraph === ''
					? []
					: [
							{
								type: 'text',
								text: paragraph,
							},
						],
		})),
	};
};

export const parseEmailBodyDocument = (value: string): TiptapDocument =>
	isTiptapDocument(value)
		? (JSON.parse(value) as TiptapDocument)
		: createTiptapDocumentFromText(value);

export const serializeTiptapDocument = (value: unknown): string =>
	JSON.stringify(value);
