import { Mark, mergeAttributes, Node } from '@tiptap/core';

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
	name: 'registrationData' | 'attendeeTable',
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
	'Registration data block',
);

export const AttendeeTableNode = createMailBlockNode(
	'attendeeTable',
	'Attendee table block',
);
