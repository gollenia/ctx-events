import {
	EMAIL_TEMPLATE_TOKEN_MENTION_ITEMS,
	type EmailTemplateMentionItem,
} from './tiptap';
import type { SubjectCommandState } from './editorTypes';

export const getSubjectCommandState = (
	value: string,
	selectionStart: number | null,
): SubjectCommandState | null => {
	const cursor = selectionStart ?? value.length;
	const textBeforeCursor = value.slice(0, cursor);
	const match = /(^|\s)@([^\n@]*)$/.exec(textBeforeCursor);

	if (!match) {
		return null;
	}

	const query = match[2] ?? '';

	return {
		query,
		from: cursor - query.length - 1,
		to: cursor,
	};
};

export const getSubjectMentionItems = (
	commandState: SubjectCommandState | null,
): EmailTemplateMentionItem[] => {
	const normalizedQuery = commandState?.query.trim().toLowerCase() ?? '';

	return EMAIL_TEMPLATE_TOKEN_MENTION_ITEMS.filter((item) =>
		normalizedQuery === '' ? true : item.searchText.includes(normalizedQuery),
	).slice(0, 8);
};
