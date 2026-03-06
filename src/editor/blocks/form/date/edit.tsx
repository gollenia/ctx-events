/**
 * Wordpress dependencies
 */

import {
	FieldHeader,
	useFieldName,
	useFieldProps,
	type VisibilityRule,
} from '@events/form';

import { TextControl } from '@wordpress/components';
/**
 * Internal dependencies
 */
import Inspector from './inspector';

export type DateAttributes = {
	name: string;
	width: number;
	required: boolean;
	min: string;
	max: string;
	label: string;
	context: string;
	defaultValue: string;
	description: string;
	visibilityRule?: VisibilityRule | null;
};

interface EditProps {
	attributes: DateAttributes;
	setAttributes: (attributes: Partial<DateAttributes>) => void;
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
			/>

			<TextControl
				autoComplete="off"
				value={attributes.defaultValue}
				type="date"
				onChange={(value) => setAttributes({ defaultValue: value })}
			/>
		</div>
	);
};

export default edit;
