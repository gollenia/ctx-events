import {
	__experimentalGetBorderClassesAndStyles as getBorderClassesAndStyles,
	getColorClassName,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import type { DetailsItemAttributes } from '@events/details/types';
import EventIcon from '../../../shared/icons/EventIcon';

type DetailsItemSaveProps = {
	attributes: DetailsItemAttributes & Record<string, unknown>;
	className?: string;
};

export default function Save(props: DetailsItemSaveProps) {
	const {
		attributes: {
			imageUrl,
			icon,
			url,
			urlIcon,
			iconColor,
			customIconColor,
			iconBackgroundColor,
			customIconBackgroundColor,
		},
		className,
	} = props;

	const classes = [className, 'event-details-item'].filter(Boolean).join(' ');
	const blockProps = useBlockProps.save({ className: classes });
	const borderProps = getBorderClassesAndStyles(props.attributes);
	const imageStyle = {
		...borderProps.style,
		...blockProps.style,
		color: customIconColor || undefined,
		backgroundColor: customIconBackgroundColor || undefined,
	};

	const imageClasses = [
		borderProps.classes,
		'event-details-image',
		getColorClassName('color', iconColor),
		getColorClassName('background-color', iconBackgroundColor),
	]
		.filter(Boolean)
		.join(' ');

	const innerBlocksProps = useInnerBlocksProps.save();

	return (
		<li {...blockProps}>
			<div className={imageClasses} style={imageStyle}>
				{imageUrl ? (
					<img src={imageUrl} alt="" />
				) : (
					<EventIcon name={icon} />
				)}
			</div>

			<div {...innerBlocksProps} className="event-details-text" />

			{url && (
				<a
					className="event-details-action"
					href={url}
					target="_blank"
					rel="noopener noreferrer"
				>
					{urlIcon && <EventIcon name={urlIcon} />}
				</a>
			)}
		</li>
	);
}
