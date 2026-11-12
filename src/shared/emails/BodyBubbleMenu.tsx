import type { Editor } from '@tiptap/react';
import {
	Button,
	ColorIndicator,
	ColorPalette,
	Popover,
	Toolbar,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';
import { createPortal, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	formatBold,
	formatItalic,
	formatUnderline,
	table,
	tableColumnAfter,
	tableColumnBefore,
	tableColumnDelete,
	tableRowAfter,
	tableRowBefore,
	tableRowDelete,
	trash,
} from '@wordpress/icons';

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
	const isTableActive = editor.isActive('table');

	return createPortal(
		<>
			<div className="ctx-email-editor__bubble-menu">
				{isTableActive ? (
					<Toolbar label={__('Table options', 'ctx-events')}>
						<ToolbarGroup>
							<ToolbarButton
								icon={tableColumnBefore}
								title={__('Add column before', 'ctx-events')}
								disabled={!editor.can().addColumnBefore()}
								onClick={() => editor.chain().focus().addColumnBefore().run()}
							/>
							<ToolbarButton
								icon={tableColumnAfter}
								title={__('Add column after', 'ctx-events')}
								disabled={!editor.can().addColumnAfter()}
								onClick={() => editor.chain().focus().addColumnAfter().run()}
							/>
							<ToolbarButton
								icon={tableColumnDelete}
								isDestructive
								title={__('Delete column', 'ctx-events')}
								disabled={!editor.can().deleteColumn()}
								onClick={() => editor.chain().focus().deleteColumn().run()}
							/>
						</ToolbarGroup>
						<ToolbarGroup>
							<ToolbarButton
								icon={tableRowBefore}
								title={__('Add row before', 'ctx-events')}
								disabled={!editor.can().addRowBefore()}
								onClick={() => editor.chain().focus().addRowBefore().run()}
							/>
							<ToolbarButton
								icon={tableRowAfter}
								title={__('Add row after', 'ctx-events')}
								disabled={!editor.can().addRowAfter()}
								onClick={() => editor.chain().focus().addRowAfter().run()}
							/>
							<ToolbarButton
								icon={tableRowDelete}
								isDestructive
								title={__('Delete row', 'ctx-events')}
								disabled={!editor.can().deleteRow()}
								onClick={() => editor.chain().focus().deleteRow().run()}
							/>
						</ToolbarGroup>
						<ToolbarGroup>
							<ToolbarButton
								icon={table}
								title={__('Toggle header row', 'ctx-events')}
								onClick={() => editor.chain().focus().toggleHeaderRow().run()}
							/>
							<ToolbarButton
								icon={trash}
								isDestructive
								title={__('Delete table', 'ctx-events')}
								disabled={!editor.can().deleteTable()}
								onClick={() => editor.chain().focus().deleteTable().run()}
							/>
						</ToolbarGroup>
					</Toolbar>
				) : (
					<>
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
					</>
				)}
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
