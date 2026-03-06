import { chipStyleForContext } from '@events/utilities';
import {
	ComboboxControl,
	Flex,
	FormTokenField,
	TextControl,
} from '@wordpress/components';
import {
	useCallback,
	useEffect,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import useOtherFormFields from '../hooks/useOtherFormFields';
import { extractContextOptions } from '../utils/extractContextOptions';

interface ContextControlProps {
	value: string;
	onChange: (value: string) => void;
	clientId: string;
}

const ContextControl = ({ value, onChange, clientId }: ContextControlProps) => {
	const otherFields = useOtherFormFields(clientId);

	console.log('Other fields:', otherFields);

	const contextOptions = useMemo(() => {
		return extractContextOptions(otherFields);
	}, [otherFields, value]);

	console.log('Context options:', contextOptions);

	return (
		<>
			<TextControl
				className="ctx:event-field__context-combobox"
				label={__('Context', 'ctx-events')}
				value={value}
				onChange={(value) => {
					onChange(value);
				}}
			/>

			{contextOptions.length > 0 && (
				<>
					<span className="ctx:event-field__context-suggestions-label">
						{__('Existing contexts:', 'ctx-events')}
					</span>
					<Flex justify="flex-start">
						{contextOptions.map((option) => {
							const chipColor = chipStyleForContext(option);
							return (
								<button
									key={option}
									type="button"
									style={chipColor}
									onClick={() => {
										console.log('Context option clicked:', option);
										onChange(option);
									}}
								>
									{option}
								</button>
							);
						})}
					</Flex>
				</>
			)}
		</>
	);
};

export default ContextControl;
