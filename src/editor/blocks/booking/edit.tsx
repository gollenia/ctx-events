import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import EventIcon from '../../../shared/icons/EventIcon';
import Inspector from './inspector';
import Toolbar from './toolbar';

type BookingAttributes = {
	buttonTitle: string;
	buttonIcon: string;
	iconRight: boolean;
	iconOnly: boolean;
};

type BookingMeta = {
	_booking_enabled?: boolean;
};

type BookingEditProps = {
	attributes: BookingAttributes;
	setAttributes: (attributes: Partial<BookingAttributes>) => void;
	className?: string;
	backgroundColor?: string;
	textColor?: string;
};

const edit = (props: BookingEditProps) => {
	const {
		attributes: { buttonTitle, buttonIcon, iconRight, iconOnly },
		setAttributes,
		className,
		backgroundColor,
		textColor,
	} = props;

	const postType = (select('core/editor') as { getCurrentPostType: () => string })
		.getCurrentPostType();
	const [meta, setMeta] = useEntityProp('postType', postType, 'meta') as [
		BookingMeta,
		(value: Partial<BookingMeta>) => void,
	];

	const blockProps = useBlockProps({
		className: 'ctx-event-booking',
	});

	const isOutline = (blockProps.className ?? '').includes('is-style-outline');
	const style = {
		...blockProps.style,
		backgroundColor: isOutline ? 'transparent' : backgroundColor,
		boxShadow: isOutline
			? `inset 0px 0px 0px 2px ${backgroundColor}`
			: 'none',
		color: isOutline ? backgroundColor : textColor,
	};

	const buttonClasses = [
		className,
		'ctx__button',
		iconOnly ? 'ctx__button--icon-only' : false,
		iconRight ? 'reverse' : false,
		meta._booking_enabled ? 'rspv-enabled' : 'rspv-disabled',
	]
		.filter(Boolean)
		.join(' ');

	return (
		<div {...blockProps}>
			<Inspector {...props} />
			<Toolbar {...props} />
			<span style={style} className={buttonClasses} aria-disabled={!meta._booking_enabled}>
				{buttonIcon && <EventIcon name={buttonIcon} />}
				<RichText
					disabled={!meta._booking_enabled}
					tagName="span"
					value={buttonTitle}
					onChange={(value) => setAttributes({ buttonTitle: value })}
					placeholder={__('Registration', 'ctx-events')}
					allowedFormats={['core/bold', 'core/italic']}
				/>
			</span>
		</div>
	);
};

export default edit;
