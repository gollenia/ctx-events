/**
 * Wordpress dependencies
 */

/**
 * Wordpress dependencies
 */

import {
	FieldHeader,
	useFieldName,
	useFieldProps,
	type VisibilityRule,
} from '@events/form';
import { RichText } from '@wordpress/block-editor';
import { CheckboxControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import icons from './icons';
/**
 * Internal dependencies
 */
import Inspector from './inspector';

export type CheckBoxAttributes = {
	context: string;
	width: number;
	required: boolean;
	variant: 'checkbox' | 'toggle';
	requiredMessage: string;
	defaultValue: boolean;
	description: string;
	name: string;
	label: string;
	visibilityRule?: VisibilityRule | null;
};

interface EditProps {
	attributes: CheckBoxAttributes;
	setAttributes: (attributes: Partial<CheckBoxAttributes>) => void;
	clientId: string;
}

const edit = (props: EditProps) => {
	const { attributes, setAttributes } = props;

	useFieldName(attributes, setAttributes);

	const blockProps = useFieldProps(attributes);

	return (
		<div {...blockProps}>
			<Inspector
				attributes={attributes}
				clientId={props.clientId}
				setAttributes={setAttributes}
			/>

			<FieldHeader
				attributes={attributes}
				setAttributes={setAttributes}
				clientId={props.clientId}
				icon={attributes.variant === 'checkbox' ? icons.checkbox : icons.toggle}
			/>

			<div className="label">
				{attributes.variant === 'checkbox' && (
					<CheckboxControl
						checked={attributes.defaultValue}
						onChange={(value: boolean) =>
							setAttributes({ defaultValue: value })
						}
					/>
				)}

				{attributes.variant === 'toggle' && (
					<ToggleControl
						label={__('Default Value', 'ctx-events')}
						checked={attributes.defaultValue}
						onChange={(value: boolean) =>
							setAttributes({ defaultValue: value })
						}
					/>
				)}
				<RichText
					tagName="p"
					className="ctx:event-details__label"
					value={attributes.description}
					required={true}
					placeholder={__(
						'What should your visitor say "yes" to?',
						'ctx-events',
					)}
					onChange={(value: string) => setAttributes({ description: value })}
				/>
			</div>
		</div>
	);
};

export default edit;
