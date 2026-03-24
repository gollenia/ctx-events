import type { MailTemplate } from '../../types/types';

export type EventMailTemplateOverride = {
	key: MailTemplate['key'];
	enabled: boolean;
	subject: string | null;
	body: string;
	replyTo: string | null;
};

export const applyMailTemplateOverrides = (
	templates: MailTemplate[],
	overrides: EventMailTemplateOverride[] = [],
): MailTemplate[] => {
	const overrideMap = new Map(overrides.map((item) => [item.key, item]));

	return templates.map((template) => {
		const override = overrideMap.get(template.key);

		if (!override) {
			return template;
		}

		return {
			...template,
			enabled: override.enabled,
			subject: override.subject,
			body: override.body,
			replyTo: override.replyTo,
			source: 'database',
			isCustomized: true,
		};
	});
};

export const createMailTemplateOverride = (
	template: MailTemplate,
): EventMailTemplateOverride => ({
	key: template.key,
	enabled: template.enabled,
	subject: template.subject,
	body: template.body,
	replyTo: template.replyTo,
});
