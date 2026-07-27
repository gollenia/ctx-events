import type { Editor } from '@tiptap/react';
import { Button, ColorIndicator, ColorPalette, Popover } from '@wordpress/components';
import { createPortal, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { formatBold, formatItalic, formatUnderline } from '@wordpress/icons';

import type { ThemeColor } from './editorTypes';

type Props = {
	editor: Editor | null;
	colors: ThemeColor[];
	element: HTMLDivElement | null;
};

const BodyBubbleMenu = ({ editor, colors, element }: Props) => {
	const colorButtonRef = useRef<HTMLButtonElement | null>(null);
	const [isColorMenuOpen, setIsColorMenuOpen] = useState(false);
	const [, setEditorVersion] = useState(0);

	useEffect(() => {
		if (!editor) {
			return;
		}

		const refresh = () => setEditorVersion((current) => current + 1);
		editor.on('transaction', refresh);
		editor.on('selectionUpdate', refresh);

		return () => {
			editor.off('transaction', refresh);
			editor.off('selectionUpdate', refresh);
		};
	}, [editor]);

	if (!editor || !element) {
		return null;
	}

	const activeColor =
		(editor.getAttributes('textColor').color as string | undefined) ?? '#1d2327';

	return createPortal(
		<>
			<div className="ctx-email-editor__bubble-menu">
				<Button
					icon={formatBold}
					label={__('Bold', 'ctx-events')}
					isPressed={editor.isActive('bold')}
					onClick={() => editor.chain().focus().toggleBold().run()}
				/>
				<Button
					icon={formatItalic}
					label={__('Italic', 'ctx-events')}
					isPressed={editor.isActive('italic')}
					onClick={() => editor.chain().focus().toggleItalic().run()}
				/>
				<Button
					icon={formatUnderline}
					label={__('Underline', 'ctx-events')}
					isPressed={editor.isActive('underline')}
					onClick={() => editor.chain().focus().toggleMark('underline').run()}
				/>
				<Button
					ref={colorButtonRef}
					label={__('Text color', 'ctx-events')}
					onClick={() => setIsColorMenuOpen((current) => !current)}
				>
					<ColorIndicator colorValue={activeColor} />
				</Button>
			</div>

			{isColorMenuOpen && colorButtonRef.current ? (
				<Popover
					anchor={colorButtonRef.current}
					placement="bottom-start"
					offset={8}
					focusOnMount={false}
					onClose={() => setIsColorMenuOpen(false)}
				>
					<div className="ctx-email-editor__color-menu">
						<ColorPalette
							colors={colors}
							value={activeColor}
							onChange={(color) => {
								if (!color) {
									editor.chain().focus().unsetMark('textColor').run();
									setIsColorMenuOpen(false);
									return;
								}

								editor.chain().focus().setMark('textColor', { color }).run();
								setIsColorMenuOpen(false);
							}}
							clearable={true}
							disableCustomColors={true}
						/>
					</div>
				</Popover>
			) : null}
		</>,
		element,
	);
};

export default BodyBubbleMenu;
