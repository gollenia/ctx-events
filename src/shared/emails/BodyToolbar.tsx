import type { Editor } from '@tiptap/react';
import {
	ColorIndicator,
	ColorPalette,
	Popover,
	Toolbar,
	ToolbarButton,
} from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { formatBold, formatItalic, formatUnderline } from '@wordpress/icons';

import type { ThemeColor } from './editorTypes';

type Props = {
	editor: Editor | null;
	colors: ThemeColor[];
};

const BodyToolbar = ({ editor, colors }: Props) => {
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
		editor.on('focus', refresh);
		editor.on('blur', refresh);

		return () => {
			editor.off('transaction', refresh);
			editor.off('selectionUpdate', refresh);
			editor.off('focus', refresh);
			editor.off('blur', refresh);
		};
	}, [editor]);

	const activeColor =
		(editor?.getAttributes('textColor').color as string | undefined) ??
		'#1d2327';

	return (
		<>
			<Toolbar label="Options">
				<ToolbarButton
					icon={formatBold}
					label="Bold"
					isActive={editor?.isActive('bold')}
					onClick={() => editor?.chain().focus().toggleBold().run()}
				/>
				<ToolbarButton
					icon={formatItalic}
					label="Italic"
					isActive={editor?.isActive('italic')}
					onClick={() => editor?.chain().focus().toggleItalic().run()}
				/>
				<ToolbarButton
					icon={formatUnderline}
					label="Underline"
					isActive={editor?.isActive('underline')}
					onClick={() => editor?.chain().focus().toggleMark('underline').run()}
				/>
				<ToolbarButton
					ref={colorButtonRef}
					label={__('Text color', 'ctx-events')}
					onClick={() => setIsColorMenuOpen((current) => !current)}
				>
					<ColorIndicator colorValue={activeColor ? activeColor : undefined} />
				</ToolbarButton>
			</Toolbar>
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
									editor?.chain().focus().unsetMark('textColor').run();
									setIsColorMenuOpen(false);
									return;
								}

								editor?.chain().focus().setMark('textColor', { color }).run();
								setIsColorMenuOpen(false);
							}}
							clearable={true}
							disableCustomColors={true}
						/>
					</div>
				</Popover>
			) : null}
		</>
	);
};

export default BodyToolbar;
