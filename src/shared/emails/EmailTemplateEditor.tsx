import { EditorContent } from '@tiptap/react';
import { Button, Flex, Notice, TextControl, ToggleControl } from '@wordpress/components';
import { useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import type { EmailTemplateEditorProps } from './editorTypes';
import BodyToolbar from './BodyToolbar';
import MentionPopover from './MentionPopover';
import SubjectField from './SubjectField';
import { getThemeTextColors } from './themeColors';
import useBodyEditor from './useBodyEditor';
import useSubjectMentions from './useSubjectMentions';

const EmailTemplateEditor = ({
	template,
	onChange,
	onSave,
	onReset,
	onClose,
	saving = false,
	error = null,
	resetLabel = __('Reset to preset', 'ctx-events'),
	saveLabel = __('Save changes', 'ctx-events'),
	closeLabel = __('Close', 'ctx-events'),
	showEnabledToggle = true,
}: EmailTemplateEditorProps) => {
	const editorSurfaceRef = useRef<HTMLDivElement | null>(null);
	const subjectInputRef = useRef<HTMLInputElement | null>(null);
	const bodyEditor = useBodyEditor({ template, onChange });
	const subjectMentions = useSubjectMentions({
		template,
		onChange,
		inputRef: subjectInputRef,
	});
	const themeTextColors = getThemeTextColors();

	return (
		<div className="ctx-email-editor">
			<p className="ctx-email-editor__description">{template.description}</p>
			{error ? (
				<Notice status="error" isDismissible={false}>
					{error}
				</Notice>
			) : null}

			{showEnabledToggle ? (
				<ToggleControl
					label={__('Enabled', 'ctx-events')}
					checked={template.enabled}
					onChange={(enabled) => onChange({ ...template, enabled })}
				/>
			) : null}

			<TextControl
				label={__('Reply-To', 'ctx-events')}
				value={template.replyTo ?? ''}
				onChange={(replyTo) => onChange({ ...template, replyTo: replyTo || null })}
			/>

			<SubjectField
				value={template.subject ?? ''}
				inputRef={subjectInputRef}
				commandOpen={
					Boolean(subjectMentions.commandState) && subjectMentions.items.length > 0
				}
				items={subjectMentions.items}
				selectedIndex={subjectMentions.selectedIndex}
				onChange={subjectMentions.handleChange}
				onCursorChange={subjectMentions.updateCommandState}
				onKeyDown={subjectMentions.handleKeyDown}
				onSelectItem={subjectMentions.insertMention}
			/>

			<div className="ctx-email-editor__field">
				<label className="ctx-email-editor__label">{__('Body', 'ctx-events')}</label>
				<p className="ctx-email-editor__hint">
					{__('Type @ for tokens and / for block inserts in the body.', 'ctx-events')}
				</p>
				<BodyToolbar editor={bodyEditor.editor} colors={themeTextColors} />
				<div className="ctx-email-editor__surface" ref={editorSurfaceRef}>
					<EditorContent editor={bodyEditor.editor} />
					{bodyEditor.commandState ? (
						<MentionPopover
							anchor={editorSurfaceRef.current}
							items={bodyEditor.items}
							selectedIndex={bodyEditor.selectedIndex}
							popoverClassName="ctx-email-editor__mentions-popover"
							onSelect={bodyEditor.insertMention}
						/>
					) : null}
				</div>
			</div>

			<div className="ctx-email-editor__actions">
				<Flex justify="space-between">
					<div>
						{onReset ? (
							<Button variant="tertiary" onClick={onReset} disabled={saving}>
								{resetLabel}
							</Button>
						) : null}
					</div>
					<Flex gap={2}>
						{onClose ? (
							<Button variant="secondary" onClick={onClose} disabled={saving}>
								{closeLabel}
							</Button>
						) : null}
						<Button variant="primary" onClick={onSave} isBusy={saving} disabled={saving}>
							{saveLabel}
						</Button>
					</Flex>
				</Flex>
			</div>
		</div>
	);
};

export default EmailTemplateEditor;
