import { useEditor } from '@tiptap/react';
import { BubbleMenu } from '@tiptap/extension-bubble-menu';
import StarterKit from '@tiptap/starter-kit';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';

import type { MailTemplate } from '../../types/types';
import {
	AttendeeTableNode,
	MailTokenNode,
	PaymentInformationNode,
	RegistrationDataNode,
	TextColorMark,
	UnderlineMark,
} from './extensions';
import { getBodyCommandState, getBodyMentionItems } from './mentionUtils';
import type { EmailTemplateMentionItem } from './tiptap';
import { parseEmailBodyDocument, serializeTiptapDocument } from './tiptap';

type Props = {
	template: MailTemplate;
	onChange: (template: MailTemplate) => void;
};

const useBodyEditor = ({ template, onChange }: Props) => {
	const bubbleMenuElement = useMemo(
		() => (typeof document === 'undefined' ? null : document.createElement('div')),
		[],
	);
	const [commandState, setCommandState] = useState<ReturnType<
		typeof getBodyCommandState
	> | null>(null);
	const [selectedIndex, setSelectedIndex] = useState(0);
	const templateRef = useRef(template);
	const onChangeRef = useRef(onChange);
	const commandStateRef = useRef(commandState);
	const itemsRef = useRef(getBodyMentionItems(commandState));
	const selectedIndexRef = useRef(selectedIndex);
	const items = getBodyMentionItems(commandState);

	useEffect(() => {
		templateRef.current = template;
		onChangeRef.current = onChange;
		commandStateRef.current = commandState;
		itemsRef.current = items;
		selectedIndexRef.current = selectedIndex;
	}, [commandState, items, onChange, selectedIndex, template]);

	const editor = useEditor({
		immediatelyRender: false,
		extensions: [
			StarterKit.configure({
				heading: false,
				blockquote: false,
				codeBlock: false,
				hardBreak: true,
				horizontalRule: false,
				strike: false,
			}),
			UnderlineMark,
			TextColorMark,
			MailTokenNode,
			RegistrationDataNode,
			AttendeeTableNode,
			PaymentInformationNode,
			BubbleMenu.configure({
				element: bubbleMenuElement,
				shouldShow: ({ editor: currentEditor, state }) =>
					currentEditor.isFocused && !state.selection.empty && currentEditor.isEditable,
				options: { placement: 'top', offset: 8 },
			}),
		],
		content: parseEmailBodyDocument(template.body),
		onUpdate: ({ editor: currentEditor }) => {
			setCommandState(getBodyCommandState(currentEditor));
			setSelectedIndex(0);

			const body = serializeTiptapDocument(currentEditor.getJSON());
			if (body === templateRef.current.body) {
				return;
			}

			onChangeRef.current({ ...templateRef.current, body });
		},
		onSelectionUpdate: ({ editor: currentEditor }) => {
			setCommandState(getBodyCommandState(currentEditor));
			setSelectedIndex(0);
		},
		editorProps: {
			handleKeyDown: (_view, event) => {
				if (!itemsRef.current.length || !commandStateRef.current) {
					return false;
				}

				if (event.key === 'ArrowDown') {
					event.preventDefault();
					setSelectedIndex((current) =>
						current >= itemsRef.current.length - 1 ? 0 : current + 1,
					);
					return true;
				}

				if (event.key === 'ArrowUp') {
					event.preventDefault();
					setSelectedIndex((current) =>
						current <= 0 ? itemsRef.current.length - 1 : current - 1,
					);
					return true;
				}

				if (event.key === 'Enter' || event.key === 'Tab') {
					event.preventDefault();
					insertMention(itemsRef.current[selectedIndexRef.current] ?? null);
					return true;
				}

				if (event.key === 'Escape') {
					event.preventDefault();
					setCommandState(null);
					setSelectedIndex(0);
					return true;
				}

				return false;
			},
		},
	});

	const insertMention = (item: EmailTemplateMentionItem | null) => {
		const currentCommandState = commandStateRef.current;

		if (!editor || !currentCommandState || !item) {
			return;
		}

		const chain = editor
			.chain()
			.focus()
			.setTextSelection({
				from: currentCommandState.from,
				to: currentCommandState.to,
			})
			.deleteRange({
				from: currentCommandState.from,
				to: currentCommandState.to,
			});

		if (item.kind === 'token') {
			chain.insertContent([
				{
					type: 'mailToken',
					attrs: {
						token: item.token,
						label: item.label,
					},
				},
				{
					type: 'text',
					text: ' ',
				},
			]);
		} else if (item.kind === 'command') {
			if (item.command === 'bulletList') {
				chain.toggleBulletList();
			} else {
				chain.toggleOrderedList();
			}
		} else {
			chain.insertContent({ type: item.type });
		}

		chain.run();
		setCommandState(null);
		setSelectedIndex(0);
	};

	useEffect(() => {
		if (!editor) {
			return;
		}

		const nextContent = parseEmailBodyDocument(template.body);
		const currentContent = serializeTiptapDocument(editor.getJSON());
		const nextSerialized = serializeTiptapDocument(nextContent);

		if (currentContent === nextSerialized) {
			return;
		}

		editor.commands.setContent(nextContent, false);
	}, [editor, template.body]);

	return {
		editor,
		commandState,
		items,
		selectedIndex,
		insertMention,
		bubbleMenuElement,
	};
};

export default useBodyEditor;
