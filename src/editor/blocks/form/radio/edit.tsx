import {
	FieldHeader,
	useFieldName,
	useFieldProps,
	type VisibilityRule,
} from '@events/form';
import Inspector from './inspector';

type RadioAttributes = {
	name: string;
	context: string;
	width: number;
	required: boolean;
	label: string;
	placeholder: number;
	description: string;
	options: string[];
	defaultValue?: number;
	visibilityRule?: VisibilityRule | null;
};

interface EditProps {
	attributes: RadioAttributes;
	setAttributes: (attributes: Partial<RadioAttributes>) => void;
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
			<div className="ctx:event-field__select">
				<fieldset>
					{attributes.options.map((option, index) => {
						const optionId = `${attributes.name}-${index}`;
						return (
							<div key={`${attributes.name}-${index}`}>
								<input
									id={optionId}
									type="radio"
									name={attributes.name}
									value={index}
									checked={attributes.defaultValue === index}
									onChange={() => {
										setAttributes({ defaultValue: index });
									}}
								/>
								<label htmlFor={optionId}>{option}</label>
							</div>
						);
					})}
				</fieldset>
			</div>
		</div>
	);
};

export default edit;
