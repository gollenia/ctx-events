import { useBlockProps } from '@wordpress/block-editor';
import { isValidSlug, isValidLabel } from '../utils/validation';

/**
 * @param {Object} attributes - Die Block-Attribute
 * @param {Object} options - Zusätzliche Optionen für useBlockProps (optional)
 */
export default function useFieldProps(attributes, options = {}) {
	const { name, label, width } = attributes;

	const isSlugValid = isValidSlug(name);
	const isLabelValid = isValidLabel(label);
	const hasError = !isSlugValid || !isLabelValid;
	const { className = '', ...restOptions } = options;

	return useBlockProps({
		...restOptions,
		className: [
			'ctx:event-field',
			`ctx:event-field--${width}`,
			hasError ? 'ctx:event-field--error' : '',
			className
		]
		.filter(Boolean)
		.join(' '),
	});
}