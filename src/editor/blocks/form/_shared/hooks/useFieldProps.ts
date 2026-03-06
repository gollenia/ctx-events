import { chipStyleForContext } from '@events/utilities';
import { useBlockProps } from '@wordpress/block-editor';
import { border } from '@wordpress/icons';
import { isValidLabel } from '../utils/validation';

interface UseFieldPropsOptions {
	className?: string;
	[key: string]: unknown;
}

type UseFieldPropsAttributes = {
	label?: string;
	width?: number;
	context?: string;
};

export default function useFieldProps(
	attributes: UseFieldPropsAttributes,
	options: UseFieldPropsOptions = {},
) {
	const { label, width, context } = attributes;

	const isLabelValid = isValidLabel(label);
	const hasError = !isLabelValid;
	const { className = '', ...restOptions } = options;
	const contextColor = context ? chipStyleForContext(context) : undefined;

	return useBlockProps({
		...restOptions,
		style: {
			gridColumn: `auto / span ${width || 6};`,
			boxShadow: hasError
				? 'inset 0 0 0 1px var(--wp--preset--color--danger)'
				: contextColor
					? `inset 0 0 0 1px ${contextColor.color}`
					: undefined,
		},
		className: [
			'ctx:event-field components-placeholder block-editor-media-placeholder is-large',
			`ctx:event-field--${width || 6}`,
			hasError ? 'ctx:event-field--error' : '',
			context ? `ctx:event-field--${context}` : '',
			className,
		]
			.filter(Boolean)
			.join(' '),
	});
}
