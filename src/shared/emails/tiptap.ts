export type TiptapDocument = {
	type: 'doc';
	content: Array<Record<string, unknown>>;
};

export const EMAIL_TEMPLATE_TOKENS = [
	{ token: '{{booking.reference}}', label: 'Booking reference' },
	{ token: '{{booking.email}}', label: 'Booking email' },
	{ token: '{{booking.first_name}}', label: 'Booking first name' },
	{ token: '{{booking.last_name}}', label: 'Booking last name' },
	{ token: '{{booking.cancellation_reason}}', label: 'Cancellation reason' },
	{ token: '{{event.name}}', label: 'Event name' },
	{ token: '{{event.start}}', label: 'Event start' },
	{ token: '{{event.end}}', label: 'Event end' },
] as const;

export const EMAIL_TEMPLATE_BLOCKS = [
	{ type: 'registrationData', label: 'Registration data' },
	{ type: 'attendeeTable', label: 'Attendee table' },
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
			command: 'bulletList' | 'orderedList';
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
			id: 'command:bulletList',
			kind: 'command' as const,
			label: 'Bullets',
			searchText: 'bullets bullet list unordered list'.toLowerCase(),
			command: 'bulletList' as const,
		},
		{
			id: 'command:orderedList',
			kind: 'command' as const,
			label: 'Numbers',
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
