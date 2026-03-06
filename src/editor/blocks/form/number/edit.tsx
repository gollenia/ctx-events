import { FieldHeader, useFieldName, useFieldProps } from '@events/form';
import { RangeControl, TextControl } from '@wordpress/components';
import Inspector from './inspector';

type NumberAttributes = {
	name: string;
	context: string;
	width: number;
	required: boolean;
	label: string;
	placeholder: number | string;
	min: number;
	max: number;
	step: number;
	range: boolean;
	defaultValue: number;
	hasLabels: boolean;
	hasTicks: boolean;
	variant: string;
	visibilityRule?: {
		field: string;
		value: 'checked' | 'unchecked';
	} | null;
};

interface EditProps {
	attributes: NumberAttributes;
	setAttributes: (attributes: Partial<NumberAttributes>) => void;
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

			{attributes.variant === 'range' ? (
				<RangeControl
					value={Number(attributes.placeholder)}
					onChange={(value) => setAttributes({ placeholder: value ?? 0 })}
					min={attributes.min}
					max={attributes.max}
					step={attributes.step}
				/>
			) : (
				<TextControl
					autoComplete="off"
					value={attributes.placeholder}
					type="number"
					onChange={(value) => setAttributes({ placeholder: value })}
				/>
			)}
		</div>
	);
};

export default edit;
