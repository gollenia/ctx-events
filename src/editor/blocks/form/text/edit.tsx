import {
	FieldHeader,
	useFieldName,
	useFieldProps,
	type VisibilityRule,
} from '@events/form';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import icon from './icon';
import Inspector from './inspector';

export type TextAttributes = {
	name: string;
	context: string;
	width: number;
	required: boolean;
	pattern: string;
	label: string;
	placeholder: string;
	visibilityRule?: VisibilityRule | null;
};

interface EditProps {
	attributes: TextAttributes;
	setAttributes: (attributes: Partial<TextAttributes>) => void;
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
				setAttributes={setAttributes}
				clientId={props.clientId}
			/>
			<FieldHeader
				attributes={attributes}
				setAttributes={setAttributes}
				clientId={props.clientId}
				icon={icon}
			/>

			<TextControl
				autoComplete="off"
				value={attributes.placeholder}
				type="text"
				width="100%"
				placeholder={__('Placeholder text', 'ctx-events')}
				onChange={(value) => setAttributes({ placeholder: value })}
			/>
		</div>
	);
};

export default edit;
