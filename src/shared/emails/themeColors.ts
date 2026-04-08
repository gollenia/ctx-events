import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import type { ThemeColor } from './editorTypes';

const DEFAULT_TEXT_COLORS: ThemeColor[] = [
	{ name: __('Text', 'ctx-events'), color: '#1d2327', slug: 'text' },
	{ name: __('Blue', 'ctx-events'), color: '#0b57d0', slug: 'blue' },
	{ name: __('Green', 'ctx-events'), color: '#137333', slug: 'green' },
	{ name: __('Orange', 'ctx-events'), color: '#b06000', slug: 'orange' },
	{ name: __('Red', 'ctx-events'), color: '#b42318', slug: 'red' },
];

type BlockEditorSettings = {
	colors?: Array<{ name?: string; color?: string; slug?: string }>;
};

export const getThemeTextColors = (): ThemeColor[] => {
	const settings = select('core/block-editor' as never)?.getSettings?.() as
		| BlockEditorSettings
		| undefined;
	const colors = settings?.colors ?? [];
	const normalizedColors = colors
		.filter((item) => typeof item?.color === 'string' && item.color.trim() !== '')
		.map((item) => ({
			name: item.name?.trim() || item.slug?.trim() || item.color!.trim(),
			color: item.color!.trim(),
			slug: item.slug?.trim(),
		}));

	return normalizedColors.length ? normalizedColors : DEFAULT_TEXT_COLORS;
};
