import {
	getColorClassName,
	useBlockProps,
	__experimentalUseBorderProps as useBorderProps,
	useInnerBlocksProps,
	withColors,
} from '@wordpress/block-editor';
import { compose } from '@wordpress/compose';
import { useRef } from '@wordpress/element';
import type {
	ColorValue,
	DetailsItemAttributes,
} from '@events/details/types';
import EventIcon from '../../../shared/icons/EventIcon';
import Inspector from './inspector';
import Toolbar from './toolbar';

type Media = {
	id?: number;
	url?: string;
	sizes?: {
		thumbnail?: {
			url?: string;
		};
	};
};

type DetailsItemEditProps = {
	attributes: DetailsItemAttributes & Record<string, unknown>;
	className?: string;
	iconColor?: ColorValue;
	iconBackgroundColor?: ColorValue;
	customIconColor?: string;
	customIconBackgroundColor?: string;
	setAttributes: (
		attributes: Partial<DetailsItemAttributes> & Record<string, unknown>,
	) => void;
};

function ItemEdit(props: DetailsItemEditProps) {
	const {
		attributes: { icon, url, urlIcon, imageUrl },
		iconColor,
		customIconColor,
		customIconBackgroundColor,
		iconBackgroundColor,
		className,
		setAttributes,
	} = props;

	const imageRef = useRef<HTMLImageElement | null>(null);

	const onSelectMedia = (media: Media) => {
		if (!media?.url) {
			setAttributes({ imageUrl: '', imageId: 0 });
			return;
		}

		setAttributes({
			imageUrl: media.sizes?.thumbnail?.url ?? media.url,
			imageId: media.id ?? 0,
		});
	};

	const classes = [className].filter(Boolean).join(' ');
	const blockProps = useBlockProps({ className: classes });
	const borderProps = useBorderProps(props.attributes);
	const imageStyle = {
		...borderProps.style,
		color: iconColor?.color ?? customIconColor ?? 'none',
		backgroundColor:
			iconBackgroundColor?.color ?? customIconBackgroundColor ?? 'none',
	};

	const imageClasses = [
		'event-details-image',
		getColorClassName('color', iconColor as unknown as string),
		getColorClassName(
			'background-color',
			iconBackgroundColor as unknown as string,
		),
	]
		.filter(Boolean)
		.join(' ');

	const template = [
		[
			'core/heading',
			{
				placeholder: 'Title',
				level: 4,
				className: 'event-details-title',
				style: { spacing: { margin: { top: '0px', bottom: '0px' } } },
			},
		],
		[
			'core/paragraph',
			{ placeholder: 'Description', className: 'event-details-data' },
		],
	] as const;

	const innerBlockProps = useInnerBlocksProps(
		{ className: 'event-details-text' },
		{
			template,
			allowedBlocks: ['core/paragraph', 'core/heading'],
		},
	);

	return (
		<div
			{...blockProps}
			style={{
				...blockProps.style,
				backgroundColor: 'none',
			}}
		>
			<Inspector {...props} />
			<Toolbar {...props} onSelectMedia={onSelectMedia} />
			<div className="event-details-item">
				<div className={imageClasses} style={imageStyle}>
					{imageUrl ? (
						<img src={imageUrl} ref={imageRef} alt="" />
					) : (
						<EventIcon name={icon} />
					)}
				</div>

				<div className="event-details-text" {...innerBlockProps} />
				{url && (
					<div className="event-details-action">
						<b><EventIcon name={urlIcon} /></b>
					</div>
				)}
			</div>
		</div>
	);
}

export default compose([
	withColors({
		iconColor: 'icon-color',
		iconBackgroundColor: 'background-color',
	}),
])(ItemEdit);
