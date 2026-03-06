import {
	FieldHeader,
	useFieldName,
	useFieldProps,
	type VisibilityRule,
} from '@events/form';
import { TextControl } from '@wordpress/components';
import Inspector from './inspector';

type PhoneAttributes = {
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
	attributes: PhoneAttributes;
	setAttributes: (attributes: Partial<PhoneAttributes>) => void;
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
			/>

			<TextControl
				autoComplete="off"
				type="tel"
				value={attributes.placeholder}
				onChange={(value) => setAttributes({ placeholder: value })}
			/>
		</div>
	);
};

export default edit;
