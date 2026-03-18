import {
	AlignmentToolbar,
	BlockControls,
	__experimentalLinkControl as LinkControl,
	MediaReplaceFlow,
} from '@wordpress/block-editor';
import { Popover, ToolbarButton } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { link } from '@wordpress/icons';
import type { DetailsItemAttributes } from '@events/details/types';

const ALLOWED_MEDIA_TYPES = ['image'];

type LinkValue = {
	url?: string;
	opensInNewTab?: boolean;
};

type ToolbarProps = {
	attributes: DetailsItemAttributes;
	setAttributes: (
		attributes: Partial<DetailsItemAttributes> & {
			focalPoint?: undefined;
		},
	) => void;
	onSelectMedia: (media: unknown) => void;
};

const Toolbar = (props: ToolbarProps) => {
	const { attributes, setAttributes, onSelectMedia } = props;
	const { textAlign, imageId, imageUrl, url, opensInNewTab, rel } = attributes;
	const [isEditingURL, setIsEditingURL] = useState(false);

	return (
		<>
			<BlockControls>
				<AlignmentToolbar
					value={textAlign}
					onChange={(value) => setAttributes({ textAlign: value })}
				/>
			</BlockControls>
			<BlockControls group="other">
				<MediaReplaceFlow
					mediaId={imageId}
					mediaURL={imageUrl}
					allowedTypes={ALLOWED_MEDIA_TYPES}
					accept="image/*,video/*"
					onSelect={onSelectMedia}
					name={
						!imageUrl
							? __('Add Media', 'ctx-events')
							: __('Replace', 'ctx-events')
					}
				/>
				{imageUrl && (
					<ToolbarButton
						icon="trash"
						title={__('Remove Media', 'ctx-events')}
						onClick={() => {
							setAttributes({
								imageId: 0,
								imageUrl: '',
								focalPoint: undefined,
							});
						}}
					/>
				)}
				<ToolbarButton
					name="link"
					icon={link}
					title={__('Link', 'ctx-events')}
					onClick={() => setIsEditingURL(true)}
				/>
			</BlockControls>
			{isEditingURL && (
				<Popover
					placement="bottom"
					onClose={() => setIsEditingURL(false)}
					focusOnMount={isEditingURL ? 'firstElement' : false}
					__unstableSlotName="__unstable-block-tools-after"
					shift
				>
					<LinkControl
						value={{ url, opensInNewTab }}
						onChange={(linkObject: LinkValue) =>
							setAttributes({
								rel,
								url: linkObject.url ?? '',
								opensInNewTab: linkObject.opensInNewTab ?? false,
							})
						}
						onRemove={() => {
							setAttributes({
								url: '',
								opensInNewTab: false,
							});
						}}
						forceIsEditingLink={isEditingURL}
					/>
				</Popover>
			)}
		</>
	);
};

export default Toolbar;
