import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { select, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import type {
	DetailsPersonAttributes,
	DetailBlockProps,
	EventSpeakerMeta,
	SpeakerRecord,
} from '@events/details/types';
import EventIcon from '../../../shared/icons/EventIcon';
import Inspector from './inspector';

const edit = (props: DetailBlockProps<DetailsPersonAttributes>) => {
	const postType = (select('core/editor') as { getCurrentPostType: () => string })
		.getCurrentPostType();
	const [meta] = useEntityProp('postType', postType, 'meta') as [
		EventSpeakerMeta,
	];
	const {
		attributes: {
			showPortrait,
			description,
			showLink,
			customSpeakerId,
			url,
			linkTo,
		},
		setAttributes,
	} = props;

	const id = customSpeakerId || meta._speaker_id;
	const speaker = useSelect(
		(selectFn) => {
			if (!id) {
				return null;
			}

			return (selectFn('core') as {
				getEntityRecord: (
					kind: string,
					name: string,
					recordId: number,
					query?: Record<string, unknown>,
				) => SpeakerRecord | null;
			}).getEntityRecord('postType', 'event-speaker', id, {
				per_page: 1,
				include: [id],
				_embed: true,
				meta: { _email: 'true' },
			});
		},
		[id],
	);

	const link = (() => {
		switch (linkTo) {
			case 'mail':
				return speaker?.meta?._email ? `mailto:${speaker.meta._email}` : null;
			case 'call':
				return speaker?.meta?._phone ? `tel:${speaker.meta._phone}` : null;
			case 'public':
				return speaker?.link ?? null;
			case 'custom':
				return url || null;
			default:
				return null;
		}
	})();

	const linkIcon = (() => {
		const socialMediaIcons = ['facebook', 'instagram', 'youtube', 'github'];

		if (linkTo === 'custom') {
			for (const socialIcon of socialMediaIcons) {
				if (url.includes(socialIcon)) {
					return socialIcon;
				}
			}
		}

		return linkTo === 'custom' || linkTo === 'public' ? 'link' : linkTo;
	})();

	const image = speaker?._embedded?.['wp:featuredmedia']?.[0]?.source_url ?? null;
	const blockProps = useBlockProps({ className: 'event-details-item' });

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<div className="event-details__icon">
				{showPortrait && image ? (
					<img src={image} alt="" />
				) : (
					<EventIcon name={speaker?.gender ?? 'male'} />
				)}
			</div>
			<div className="event-details-text">
				<RichText
					tagName="h4"
					className="event-details_title description-editable"
					placeholder={__('Speaker', 'ctx-events')}
					value={description}
					onChange={(value) => {
						setAttributes({ description: value });
					}}
				/>
				<span className="event-details_audience">
					{speaker?.title?.rendered}
				</span>
			</div>
			{showLink && link && (
				<div className="event-details-action">
					<a href={link}>
						<EventIcon name={linkIcon} />
					</a>
				</div>
			)}
		</div>
	);
};

export default edit;
