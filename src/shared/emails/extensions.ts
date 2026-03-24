import { mergeAttributes, Node } from '@tiptap/core';

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
