import { Flex } from '../Flex';

type SubmitProps = {
	label: string;
	width: number;
	alignment: 'left' | 'center' | 'right';
	disabled: boolean;
	placeholder?: string;
	type?: 'submit' | 'button' | 'reset';
};

const Submit = (props: SubmitProps) => {
	const {
		label,
		width,
		alignment,
		disabled,
		placeholder,
	} = props;

	const className = 'ctx-form-field';

	return (
		<Flex
			className={className}
			align="center"
			justify={
				alignment === 'right'
					? 'flex-end'
					: alignment === 'center'
						? 'center'
						: 'flex-start'
			}
		>
			<input
				className="ctx-button"
				type={props.type ?? 'submit'}
				value={label || placeholder}
				disabled={disabled}
			/>
		</Flex>
	);
};

Submit.defaultProps = {
	label: '',
	width: 6,
};

export default Submit;
