import {
	Button,
	Flex,
	Notice,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import type { MailTemplate } from '../../types/types';
import {
	EMAIL_TEMPLATE_BLOCKS,
	EMAIL_TEMPLATE_TOKENS,
	parseEmailBodyDocument,
	serializeTiptapDocument,
} from './tiptap';
import {
	AttendeeTableNode,
	MailTokenNode,
	RegistrationDataNode,
} from './extensions';

type Props = {
	template: MailTemplate;
	onChange: (template: MailTemplate) => void;
	onSave: () => void;
	onReset?: () => void;
	onClose?: () => void;
	saving?: boolean;
	error?: string | null;
	resetLabel?: string;
	saveLabel?: string;
	closeLabel?: string;
};

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
}: Props) => {
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
			MailTokenNode,
			RegistrationDataNode,
			AttendeeTableNode,
		],
		content: parseEmailBodyDocument(template.body),
		onUpdate: ({ editor: currentEditor }) => {
			const body = serializeTiptapDocument(currentEditor.getJSON());

			if (body === template.body) {
				return;
			}

			onChange({ ...template, body });
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

	const insertSubjectToken = (token: string) => {
		const subject = template.subject ?? '';
		onChange({ ...template, subject: `${subject}${token}` || token });
	};

	return (
		<div className="ctx-email-editor">
			<p className="ctx-email-editor__description">{template.description}</p>
			<Notice status="info" isDismissible={false}>
				{__(
					'This template is edited as a Tiptap document and rendered server-side for outgoing mails.',
					'ctx-events',
				)}
			</Notice>
			{error ? (
				<Notice status="error" isDismissible={false}>
					{error}
				</Notice>
			) : null}

			<ToggleControl
				label={__('Enabled', 'ctx-events')}
				checked={template.enabled}
				onChange={(enabled) => onChange({ ...template, enabled })}
			/>

			<TextControl
				label={__('Reply-To', 'ctx-events')}
				value={template.replyTo ?? ''}
				onChange={(replyTo) =>
					onChange({ ...template, replyTo: replyTo || null })
				}
			/>

			<TextControl
				label={__('Subject', 'ctx-events')}
				value={template.subject ?? ''}
				onChange={(subject) => onChange({ ...template, subject: subject || null })}
			/>
			<div className="ctx-email-editor__tokens">
				<span className="ctx-email-editor__tokens-label">
					{__('Insert subject token:', 'ctx-events')}
				</span>
				{EMAIL_TEMPLATE_TOKENS.map((token) => (
					<Button
						key={`subject-${token.token}`}
						variant="tertiary"
						onClick={() => insertSubjectToken(token.token)}
					>
						{token.label}
					</Button>
				))}
			</div>

			<div className="ctx-email-editor__field">
				<label className="ctx-email-editor__label">{__('Body', 'ctx-events')}</label>
				<div className="ctx-email-editor__toolbar">
					<Button
						variant="secondary"
						onClick={() => editor?.chain().focus().toggleBold().run()}
						pressed={editor?.isActive('bold')}
					>
						{__('Bold', 'ctx-events')}
					</Button>
					<Button
						variant="secondary"
						onClick={() => editor?.chain().focus().toggleItalic().run()}
						pressed={editor?.isActive('italic')}
					>
						{__('Italic', 'ctx-events')}
					</Button>
					<Button
						variant="secondary"
						onClick={() => editor?.chain().focus().toggleBulletList().run()}
						pressed={editor?.isActive('bulletList')}
					>
						{__('Bullets', 'ctx-events')}
					</Button>
					<Button
						variant="secondary"
						onClick={() => editor?.chain().focus().toggleOrderedList().run()}
						pressed={editor?.isActive('orderedList')}
					>
						{__('Numbers', 'ctx-events')}
					</Button>
				</div>
				<div className="ctx-email-editor__tokens">
					<span className="ctx-email-editor__tokens-label">
						{__('Insert token:', 'ctx-events')}
					</span>
					{EMAIL_TEMPLATE_TOKENS.map((token) => (
						<Button
							key={token.token}
							variant="tertiary"
							onClick={() =>
								editor
									?.chain()
									.focus()
									.insertContent({
										type: 'mailToken',
										attrs: token,
									})
									.run()
							}
						>
							{token.label}
						</Button>
					))}
				</div>
				<div className="ctx-email-editor__tokens">
					<span className="ctx-email-editor__tokens-label">
						{__('Insert block:', 'ctx-events')}
					</span>
					{EMAIL_TEMPLATE_BLOCKS.map((block) => (
						<Button
							key={block.type}
							variant="tertiary"
							onClick={() =>
								editor?.chain().focus().insertContent({ type: block.type }).run()
							}
						>
							{block.label}
						</Button>
					))}
				</div>
				<div className="ctx-email-editor__surface">
					<EditorContent editor={editor} />
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
						<Button
							variant="primary"
							onClick={onSave}
							isBusy={saving}
							disabled={saving}
						>
							{saveLabel}
						</Button>
					</Flex>
				</Flex>
			</div>
		</div>
	);
};

export default EmailTemplateEditor;
