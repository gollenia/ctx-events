import { useState } from '@wordpress/element';
import type { KeyboardEvent, RefObject } from 'react';

import type { MailTemplate } from '../../types/types';
import type { EmailTemplateMentionItem } from './tiptap';
import { getSubjectCommandState, getSubjectMentionItems } from './mentionUtils';

type Props = {
	template: MailTemplate;
	onChange: (template: MailTemplate) => void;
	inputRef: RefObject<HTMLInputElement | null>;
};

const useSubjectMentions = ({ template, onChange, inputRef }: Props) => {
	const [commandState, setCommandState] = useState(
		getSubjectCommandState(template.subject ?? '', null),
	);
	const [selectedIndex, setSelectedIndex] = useState(0);
	const items = getSubjectMentionItems(commandState);

	const updateCommandState = (value: string, selectionStart: number | null) => {
		setCommandState(getSubjectCommandState(value, selectionStart));
		setSelectedIndex(0);
	};

	const insertMention = (item: EmailTemplateMentionItem | null) => {
		if (!item || item.kind !== 'token' || !commandState) {
			return;
		}

		const subject = template.subject ?? '';
		const nextSubject = `${subject.slice(0, commandState.from)}${item.token}${subject.slice(commandState.to)}`;

		onChange({ ...template, subject: nextSubject });
		setCommandState(null);
		setSelectedIndex(0);

		window.requestAnimationFrame(() => {
			const nextCursor = commandState.from + item.token.length;
			inputRef.current?.focus();
			inputRef.current?.setSelectionRange(nextCursor, nextCursor);
		});
	};

	const handleChange = (value: string, selectionStart: number | null) => {
		onChange({ ...template, subject: value || null });
		updateCommandState(value, selectionStart);
	};

	const handleKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
		if (!items.length || !commandState) {
			return;
		}

		if (event.key === 'ArrowDown') {
			event.preventDefault();
			setSelectedIndex((current) => (current >= items.length - 1 ? 0 : current + 1));
			return;
		}

		if (event.key === 'ArrowUp') {
			event.preventDefault();
			setSelectedIndex((current) => (current <= 0 ? items.length - 1 : current - 1));
			return;
		}

		if (event.key === 'Enter' || event.key === 'Tab') {
			event.preventDefault();
			insertMention(items[selectedIndex] ?? null);
			return;
		}

		if (event.key === 'Escape') {
			event.preventDefault();
			setCommandState(null);
			setSelectedIndex(0);
		}
	};

	return {
		commandState,
		items,
		selectedIndex,
		handleChange,
		handleKeyDown,
		updateCommandState,
		insertMention,
	};
};

export default useSubjectMentions;
