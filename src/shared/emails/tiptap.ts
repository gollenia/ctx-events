export type TiptapDocument = {
	type: 'doc';
	content: Array<Record<string, unknown>>;
};

export const EMAIL_TEMPLATE_TOKENS = [
	{ token: '{{booking.reference}}', label: 'Booking reference' },
	{ token: '{{booking.email}}', label: 'Booking email' },
	{ token: '{{booking.first_name}}', label: 'Booking first name' },
	{ token: '{{booking.last_name}}', label: 'Booking last name' },
	{ token: '{{event.name}}', label: 'Event name' },
	{ token: '{{event.start}}', label: 'Event start' },
	{ token: '{{event.end}}', label: 'Event end' },
] as const;

export const EMAIL_TEMPLATE_BLOCKS = [
	{ type: 'registrationData', label: 'Registration data' },
	{ type: 'attendeeTable', label: 'Attendee table' },
] as const;

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
