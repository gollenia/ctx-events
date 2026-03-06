import { chipStyleForContext } from '@events/utilities';
import { RichText } from '@wordpress/block-editor';
import {
	Flex,
	FlexItem,
	Icon,
	type IconType,
	ToggleControl,
	Tooltip,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import useDependencyLock from '../hooks/useDependencyLock';
import { isSlugLocked, isValidLabel } from '../utils/validation';
import lock from './lockIcon'; // Pfad anpassen

type FieldHeaderAttributes = {
	required: boolean;
	label: string;
	name: string;
	context?: string;
};

interface FieldHeaderProps {
	attributes: FieldHeaderAttributes;
	setAttributes: (attributes: Partial<FieldHeaderAttributes>) => void;
	clientId: string;
	icon?: IconType | null;
	helpText?: string | null;
}

const FieldHeader = ({
	attributes,
	setAttributes,
	clientId,
	icon = null,
}: FieldHeaderProps) => {
	const { label, name, required, context } = attributes;

	const contextStyle = context ? chipStyleForContext(context) : undefined;

	const isSystemLocked = isSlugLocked(name);
	const isReferenced = useDependencyLock(clientId, name);

	const isLabelValid = isValidLabel(label);

	let lockReason = '';
	if (isSystemLocked) {
		lockReason += __('System field: Cannot be changed.', 'ctx-events');
	}
	if (isReferenced) {
		lockReason += __(
			'Locked: Used by another field as visibility condition.',
			'ctx-events',
		);
	}

	return (
		<Flex
			align="center"
			justify="flex-start"
			className="components-placeholder__label"
		>
			{icon && <Icon icon={icon} width={24} height={24} />}
			<FlexItem>
				<RichText
					tagName="span"
					className={`ctx:label-input ${!isLabelValid ? 'ctx:input-error' : ''}`}
					value={label}
					placeholder={__('Enter label here...', 'ctx-events')}
					onChange={(value) => setAttributes({ label: value })}
					allowedFormats={[]}
				/>
				<span>{required ? '*' : ''}</span>
			</FlexItem>
			{context && (
				<FlexItem>
					<span
						className="context-slug"
						style={{
							...contextStyle,
							paddingLeft: '4px',
							paddingRight: '4px',
							paddingTop: '1px',
							paddingBottom: '3px',
							borderRadius: '4px',
						}}
					>
						{context}
					</span>
				</FlexItem>
			)}
		</Flex>
	);
};

export default FieldHeader;
