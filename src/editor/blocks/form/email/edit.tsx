import {
	FieldHeader,
	useFieldName,
	useFieldProps,
	type VisibilityRule,
} from '@events/form';
import { TextControl } from '@wordpress/components';
import icon from './icon';
import Inspector from './inspector';
export type EmailAttributes = {
	name: string;
	context: string;
	width: number;
	required: boolean;
	label: string;
	placeholder: string;
	description: string;
	visibilityRule?: VisibilityRule | null;
};

interface EditProps {
	attributes: EmailAttributes;
	setAttributes: (attributes: Partial<EmailAttributes>) => void;
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
				icon={icon}
			/>

			<TextControl
				autoComplete="off"
				type="email"
				value={attributes.placeholder}
				onChange={(value) => setAttributes({ placeholder: value })}
			/>
		</div>
	);
};

export default edit;
