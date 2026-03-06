import {
	FieldHeader,
	useFieldName,
	useFieldProps,
	type VisibilityRule,
} from '@events/form';
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import Inspector from './inspector';

type SelectAttributes = {
	name: string;
	context: string;
	width: number;
	required: boolean;
	options: string[];
	hasEmptyOption: boolean;
	label: string;
	placeholder: string;
	description: string;
	defaultValue?: string;
	visibilityRule?: VisibilityRule | null;
};

interface EditProps {
	attributes: SelectAttributes;
	setAttributes: (attributes: Partial<SelectAttributes>) => void;
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

			<SelectControl
				value={attributes.defaultValue}
				onChange={(value) => setAttributes({ defaultValue: value })}
			>
				{attributes.hasEmptyOption && (
					<option value="">{__('Make a selection', 'ctx-events')}</option>
				)}
				{attributes.options.map((option, index) => {
					return (
						<option key={index} value={String(index)}>
							{option}
						</option>
					);
				})}
			</SelectControl>
		</div>
	);
};

export default edit;
