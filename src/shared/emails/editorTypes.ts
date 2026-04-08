import type { MailTemplate } from '../../types/types';
import type { EmailTemplateMentionItem } from './tiptap';

export type EmailTemplateEditorProps = {
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
	showEnabledToggle?: boolean;
};

export type ThemeColor = {
	name: string;
	color: string;
	slug?: string;
};

export type BodyCommandState = {
	trigger: '@' | '/';
	query: string;
	from: number;
	to: number;
};

export type SubjectCommandState = {
	query: string;
	from: number;
	to: number;
};

export type MentionListState = {
	items: EmailTemplateMentionItem[];
	selectedIndex: number;
};
