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

	const classes = [className, 'event-details__item'].filter(Boolean).join(' ');
	const blockProps = useBlockProps({ className: classes });
	const borderProps = useBorderProps(props.attributes);
	const imageStyle = {
		...borderProps.style,
		color: iconColor?.color ?? customIconColor ?? 'none',
		backgroundColor:
			iconBackgroundColor?.color ?? customIconBackgroundColor ?? 'none',
	};

	const imageClasses = [
		'event-details__icon',
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
				className: 'event-details_title',
				style: { spacing: { margin: { top: '0px', bottom: '0px' } } },
			},
		],
		[
			'core/paragraph',
			{ placeholder: 'Description', className: 'event-details_text' },
		],
	] as const;

	const innerBlockProps = useInnerBlocksProps(
		{ className: 'event-details__item-content' },
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
			<div className={imageClasses} style={imageStyle}>
				{imageUrl ? (
					<img src={imageUrl} ref={imageRef} alt="" />
				) : (
					<i className="material-icons material-symbols-outlined">{icon}</i>
				)}
			</div>

			<div className="event-details__item-content" {...innerBlockProps} />
			{url && (
				<div className="event-details__item-action">
					<b>
						<i className="material-icons material-symbols-outlined">
							{urlIcon}
						</i>
					</b>
				</div>
			)}
		</div>
	);
}

export default compose([
	withColors({
		iconColor: 'icon-color',
		iconBackgroundColor: 'background-color',
	}),
])(ItemEdit);
