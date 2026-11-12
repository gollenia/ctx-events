import { useEditor } from '@tiptap/react';
import { BubbleMenu } from '@tiptap/extension-bubble-menu';
import { Table } from '@tiptap/extension-table';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TableRow from '@tiptap/extension-table-row';
import StarterKit from '@tiptap/starter-kit';
import { useEffect, useMemo, useRef } from '@wordpress/element';

import type { MailTemplate } from '../../types/types';
import {
	AttendeeTableNode,
	ColorBoxNode,
	MailTokenNode,
	PaymentInformationNode,
	RegistrationDataNode,
	EmailSuggestions,
	TextColorMark,
	UnderlineMark,
} from './extensions';
import { parseEmailBodyDocument, serializeTiptapDocument } from './tiptap';

type Props = {
	template: MailTemplate;
	onChange: (template: MailTemplate) => void;
};

const getActiveTableElement = (): HTMLTableElement | null => {
	const selection = window.getSelection();
	const node = selection?.anchorNode;
	const element =
		node?.nodeType === Node.ELEMENT_NODE
			? (node as Element)
			: node?.parentElement;

	return element?.closest<HTMLTableElement>('table') ?? null;
};

const useBodyEditor = ({ template, onChange }: Props) => {
	const bubbleMenuElement = useMemo(
		() => (typeof document === 'undefined' ? null : document.createElement('div')),
		[],
	);
	const templateRef = useRef(template);
	const onChangeRef = useRef(onChange);

	useEffect(() => {
		templateRef.current = template;
		onChangeRef.current = onChange;
	}, [onChange, template]);

	const editor = useEditor({
		immediatelyRender: false,
		extensions: [
			StarterKit.configure({
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
			ColorBoxNode,
			Table.configure({ resizable: true }),
			TableRow,
			TableHeader,
			TableCell,
			EmailSuggestions,
			BubbleMenu.configure({
				element: bubbleMenuElement,
				getReferencedVirtualElement: () => {
					const table = getActiveTableElement();

					return table
						? {
								getBoundingClientRect: () => table.getBoundingClientRect(),
								contextElement: table,
							}
						: null;
				},
				shouldShow: ({ editor: currentEditor, state }) =>
					currentEditor.isFocused &&
					currentEditor.isEditable &&
					(currentEditor.isActive('table') || !state.selection.empty),
				options: { placement: 'top-start', offset: 0 },
			}),
		],
		content: parseEmailBodyDocument(template.body),
		onUpdate: ({ editor: currentEditor }) => {
			const body = serializeTiptapDocument(currentEditor.getJSON());
			if (body === templateRef.current.body) {
				return;
			}

			onChangeRef.current({ ...templateRef.current, body });
		},
	});

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
		bubbleMenuElement,
	};
};

export default useBodyEditor;
