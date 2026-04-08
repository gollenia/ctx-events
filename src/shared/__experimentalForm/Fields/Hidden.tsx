type HiddenInputProps = {
	name: string;
	defaultValue?: string;
};

const HiddenInput = ({ defaultValue = '', name = '' }: HiddenInputProps) => {
	return <input defaultValue={defaultValue} name={name} type="hidden" />;
};

export default HiddenInput;
