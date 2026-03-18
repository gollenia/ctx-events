import { AlignmentControl, BlockControls } from '@wordpress/block-editor';
import { ToolbarButton } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { pullLeft, pullRight, seen, unseen } from '@wordpress/icons';

type BookingAttributes = {
	iconRight: boolean;
	iconOnly: boolean;
	buttonIcon?: string;
};

type ToolbarProps = {
	attributes: BookingAttributes;
	setAttributes: (attributes: Partial<BookingAttributes>) => void;
};

const Toolbar = (props: ToolbarProps) => {
	const {
		attributes: { iconRight, iconOnly, buttonIcon },
		setAttributes,
	} = props;

	return (
		<BlockControls group="block">
			<AlignmentControl
				value={iconRight ? 'right' : 'left'}
				onChange={(value) => {
					setAttributes({
						iconRight: value === 'right',
					});
				}}
				alignmentControls={[
					{
						icon: pullLeft,
						title: __('Align icon left', 'ctx-events'),
						align: 'left',
					},
					{
						icon: pullRight,
						title: __('Align icon right', 'ctx-events'),
						align: 'right',
					},
				]}
				label={__('Icon alignment', 'ctx-events')}
			/>
			{buttonIcon && (
				<ToolbarButton
					name="iconOnly"
					icon={iconOnly ? unseen : seen}
					title={__('Hide text', 'ctx-events')}
					isActive={iconOnly}
					onClick={() => setAttributes({ iconOnly: !iconOnly })}
				/>
			)}
		</BlockControls>
	);
};

export default Toolbar;
