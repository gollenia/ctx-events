import { Extension, Mark, mergeAttributes, Node, type Editor, type Range } from '@tiptap/core';
import Suggestion, {
	type SuggestionProps,
} from '@tiptap/suggestion';
import { PluginKey } from '@tiptap/pm/state';
import { Icon, type IconType } from '@wordpress/components';
import { createElement, renderToString } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	calendar,
	caution,
	check,
	commentAuthorName,
	commentContent,
	commentEditLink,
	envelope,
	formatListBullets,
	formatListNumbered,
	headingLevel1,
	headingLevel2,
	headingLevel3,
	info,
	link,
	mapMarker,
	payment,
	people,
	table,
	termName,
	time,
} from '@wordpress/icons';
import type { EmailTemplateMentionItem } from './tiptap';
import {
	EMAIL_TEMPLATE_BLOCK_MENTION_ITEMS,
	EMAIL_TEMPLATE_TOKEN_MENTION_ITEMS,
} from './tiptap';

export const UnderlineMark = Mark.create({
	name: 'underline',

	parseHTML() {
		return [
			{ tag: 'u' },
			{
				tag: 'span[style]',
				getAttrs: (element) =>
					(element as HTMLElement).style.textDecoration.includes('underline')
						? {}
						: false,
			},
		];
	},

	renderHTML({ HTMLAttributes }) {
		return ['u', mergeAttributes(HTMLAttributes), 0];
	},

	addCommands() {
		return {
			toggleUnderline:
				() =>
				({ commands }) =>
					commands.toggleMark(this.name),
		};
	},
});

export const TextColorMark = Mark.create({
	name: 'textColor',

	addAttributes() {
		return {
			color: {
				default: null,
				parseHTML: (element) =>
					element.getAttribute('data-color') || element.style.color || null,
				renderHTML: (attributes) => {
					if (!attributes.color) {
						return {};
					}

					return {
						'data-color': attributes.color,
						style: `color: ${attributes.color}`,
					};
				},
			},
		};
	},

	parseHTML() {
		return [{ tag: 'span[data-color]' }, { tag: 'span[style*="color"]' }];
	},

	renderHTML({ HTMLAttributes }) {
		return ['span', mergeAttributes(HTMLAttributes), 0];
	},

	addCommands() {
		return {
			setTextColor:
				(color: string) =>
				({ commands }) =>
					commands.setMark(this.name, { color }),
			unsetTextColor:
				() =>
				({ commands }) =>
					commands.unsetMark(this.name),
		};
	},
});

export const MailTokenNode = Node.create({
	name: 'mailToken',
	group: 'inline',
	inline: true,
	atom: true,
	selectable: true,

	addAttributes() {
		return {
			token: {
				default: '',
				parseHTML: (element) => element.getAttribute('data-token'),
				renderHTML: (attributes) => ({
					'data-token': attributes.token,
				}),
			},
			label: {
				default: '',
				parseHTML: (element) => element.getAttribute('data-label'),
				renderHTML: (attributes) => ({
					'data-label': attributes.label,
				}),
			},
		};
	},

	parseHTML() {
		return [{ tag: 'span[data-type="mail-token"]' }];
	},

	renderHTML({ HTMLAttributes, node }) {
		return [
			'span',
			mergeAttributes(HTMLAttributes, {
				'data-type': 'mail-token',
				class: 'ctx-email-token',
			}),
			node.attrs.label || node.attrs.token,
		];
	},

	renderText({ node }) {
		return String(node.attrs.token || '');
	},
});

const createMailBlockNode = (
	name: 'registrationData' | 'attendeeTable' | 'paymentInformation',
	label: string,
) =>
	Node.create({
		name,
		group: 'block',
		atom: true,
		selectable: true,

		parseHTML() {
			return [{ tag: `div[data-type="${name}"]` }];
		},

		renderHTML({ HTMLAttributes }) {
			return [
				'div',
				mergeAttributes(HTMLAttributes, {
					'data-type': name,
					class: 'ctx-email-block',
				}),
				label,
			];
		},
	});

export const RegistrationDataNode = createMailBlockNode(
	'registrationData',
	__('Registration data block', 'ctx-events'),
);

export const AttendeeTableNode = createMailBlockNode(
	'attendeeTable',
	__('Attendee table block', 'ctx-events'),
);

export const PaymentInformationNode = createMailBlockNode(
	'paymentInformation',
	__('Payment information block', 'ctx-events'),
);

export const ColorBoxNode = Node.create({
	name: 'colorBox',
	group: 'block',
	content: 'inline*',
	defining: true,

	addAttributes() {
		return {
			variant: {
				default: 'info',
				parseHTML: (element) => element.getAttribute('data-variant') ?? 'info',
				renderHTML: (attributes) => ({ 'data-variant': attributes.variant }),
			},
		};
	},

	parseHTML() {
		return [{ tag: 'div[data-type="color-box"]' }];
	},

	renderHTML({ HTMLAttributes }) {
		return [
			'div',
			mergeAttributes(HTMLAttributes, {
				'data-type': 'color-box',
				class: 'ctx-email-color-box',
			}),
			0,
		];
	},
});

const insertSuggestionItem = ({
	editor,
	range,
	item,
}: {
	editor: Editor;
	range: Range;
	item: EmailTemplateMentionItem;
}) => {
	const chain = editor.chain().focus().deleteRange(range);

	if (item.kind === 'token') {
		chain.insertContent([
			{ type: 'mailToken', attrs: { token: item.token, label: item.label } },
			{ type: 'text', text: ' ' },
		]);
	} else if (item.kind === 'command') {
		if (item.command === 'bulletList') {
			chain.toggleBulletList();
		} else if (item.command === 'orderedList') {
			chain.toggleOrderedList();
		} else if (item.command.startsWith('heading')) {
			chain.setNode('heading', { level: Number(item.command.at(-1)) });
		} else if (item.command === 'insertTable') {
			chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true });
		} else {
			const variant = item.command.replace('colorBox', '').toLowerCase();
			chain.insertContent({ type: 'colorBox', attrs: { variant } });
		}
	} else {
		chain.insertContent({ type: item.type });
	}

	chain.run();
};

const ITEM_ICONS: Record<string, IconType> = {
	'token:{{booking.reference}}': link,
	'token:{{booking.email}}': envelope,
	'token:{{booking.first_name}}': commentAuthorName,
	'token:{{booking.last_name}}': commentAuthorName,
	'token:{{booking.price}}': payment,
	'token:{{booking.cancellation_reason}}': commentContent,
	'token:{{event.name}}': termName,
	'token:{{event.start}}': calendar,
	'token:{{event.end}}': time,
	'token:{{event.location}}': mapMarker,
	'token:{{event.speaker}}': commentAuthorName,
	'block:registrationData': commentEditLink,
	'block:attendeeTable': people,
	'block:paymentInformation': payment,
	'command:heading1': headingLevel1,
	'command:heading2': headingLevel2,
	'command:heading3': headingLevel3,
	'command:table': table,
	'command:color-box-info': info,
	'command:color-box-warning': caution,
	'command:color-box-success': check,
	'command:bulletList': formatListBullets,
	'command:orderedList': formatListNumbered,
};

const isFormatItem = (item: EmailTemplateMentionItem) =>
	item.kind === 'command' &&
	(item.command.startsWith('heading') ||
		item.command === 'insertTable' ||
		item.command.startsWith('colorBox') ||
		item.command === 'bulletList' ||
		item.command === 'orderedList');

const createSuggestionButton = (
	item: EmailTemplateMentionItem,
	index: number,
	selectedIndex: number,
	onSelect: () => void,
) => {
	const button = document.createElement('button');
	const icon = ITEM_ICONS[item.id];
	button.type = 'button';
	button.className = 'components-button ctx-email-editor__mention-item';
	button.classList.toggle('is-primary', index === selectedIndex);
	button.addEventListener('mousedown', (event) => event.preventDefault());
	button.addEventListener('click', onSelect);

	if (icon) {
		const iconElement = document.createElement('span');
		iconElement.className = 'ctx-email-editor__mention-icon';
		iconElement.innerHTML = renderToString(createElement(Icon, { icon, size: 20 }));
		button.append(iconElement);
	}

	button.append(document.createTextNode(item.label));
	return button;
};

const createSuggestionMenu = () => {
	let element: HTMLDivElement | null = null;
	let props: SuggestionProps<EmailTemplateMentionItem> | null = null;
	let selectedIndex = 0;
	let unmount: (() => void) | null = null;

	const render = () => {
		if (!element || !props) return;
		const formatItems = props.items.filter(isFormatItem);
		const contentItems = props.items.filter((item) => !isFormatItem(item));
		const groups = [formatItems, contentItems].filter((items) => items.length > 0);
		const children: HTMLElement[] = [];

		groups.forEach((items, groupIndex) => {
			if (groupIndex > 0) {
				const separator = document.createElement('div');
				separator.className = 'ctx-email-editor__mention-separator';
				separator.setAttribute('role', 'separator');
				children.push(separator);
			}

			const group = document.createElement('div');
			group.className = 'ctx-email-editor__mention-group';
			items.forEach((item) => {
				const index = props?.items.indexOf(item) ?? 0;
				group.append(
					createSuggestionButton(item, index, selectedIndex, () => props?.command(item)),
				);
			});
			children.push(group);
		});

		element.replaceChildren(...children);
	};

	return {
		onStart(nextProps: SuggestionProps<EmailTemplateMentionItem>) {
			props = nextProps;
			selectedIndex = 0;
			element = document.createElement('div');
			element.className = 'ctx-email-editor__mentions';
			element.setAttribute('role', 'listbox');
			render();
			unmount = nextProps.mount(element);
		},
		onUpdate(nextProps: SuggestionProps<EmailTemplateMentionItem>) {
			props = nextProps;
			selectedIndex = 0;
			render();
		},
		onKeyDown({ event }: { event: KeyboardEvent }) {
			if (!props || props.items.length === 0) return false;
			if (event.key === 'ArrowDown') {
				event.preventDefault();
				selectedIndex = (selectedIndex + 1) % props.items.length;
				render();
				return true;
			}
			if (event.key === 'ArrowUp') {
				event.preventDefault();
				selectedIndex = (selectedIndex - 1 + props.items.length) % props.items.length;
				render();
				return true;
			}
			if (event.key === 'Enter' || event.key === 'Tab') {
				event.preventDefault();
				props.command(props.items[selectedIndex]);
				return true;
			}
			return false;
		},
		onExit() {
			unmount?.();
			unmount = null;
			element = null;
			props = null;
		},
	};
};

const createEmailSuggestion = (
	char: '@' | '/',
	items: EmailTemplateMentionItem[],
	pluginKey: string,
	editor: Editor,
) =>
	Suggestion<EmailTemplateMentionItem, EmailTemplateMentionItem>({
		editor,
		char,
		pluginKey: new PluginKey(pluginKey),
		allowedPrefixes: [' ', '\n'],
		items: ({ query }) => {
			const normalizedQuery = query.trim().toLowerCase();
			return items.filter((item) => item.searchText.includes(normalizedQuery)).slice(0, 8);
		},
		command: ({ editor, range, props }) => insertSuggestionItem({ editor, range, item: props }),
		render: createSuggestionMenu,
	});

export const EmailSuggestions = Extension.create({
	name: 'emailSuggestions',
	addProseMirrorPlugins() {
		return [
			createEmailSuggestion(
				'@',
				EMAIL_TEMPLATE_TOKEN_MENTION_ITEMS,
				'emailTokenSuggestion',
				this.editor,
			),
			createEmailSuggestion(
				'/',
				EMAIL_TEMPLATE_BLOCK_MENTION_ITEMS,
				'emailBlockSuggestion',
				this.editor,
			),
		];
	},
});
