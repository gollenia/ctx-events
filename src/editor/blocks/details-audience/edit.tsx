import type {
	DetailBlockProps,
	DetailsAudienceAttributes,
	EventAudienceMeta,
} from '@events/details/types';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import EventIcon from '../../../shared/icons/EventIcon';
import Inspector from './inspector';

const edit = (props: DetailBlockProps<DetailsAudienceAttributes>) => {
	const postType = (
		select('core/editor') as { getCurrentPostType: () => string }
	).getCurrentPostType();

	if (postType !== 'ctx-event') {
		return null;
	}

	const [meta, setMeta] = useEntityProp('postType', postType, 'meta') as [
		EventAudienceMeta,
		(value: Partial<EventAudienceMeta>) => void,
	];
	const {
		attributes: { description },
		setAttributes,
	} = props;
	const blockProps = useBlockProps();
	const audience = meta._event_audience?.trim() || (() => {
		const minimumAge = Number(meta._minimum_age ?? 0);
		const maximumAge = Number(meta._maximum_age ?? 0);
		if (minimumAge > 0 && maximumAge > 0) return `${minimumAge}–${maximumAge} ${__('years', 'ctx-events')}`;
		if (minimumAge > 0) return `${__('from', 'ctx-events')} ${minimumAge} ${__('years', 'ctx-events')}`;
		if (maximumAge > 0) return `${__('up to', 'ctx-events')} ${maximumAge} ${__('years', 'ctx-events')}`;
		return '';
	})();

	return (
		<div {...blockProps}>
			<Inspector />

			<div className="event-details-item">
				<div className="event-details-image">
					<EventIcon name="audience" />
				</div>
				<div className="event-details-text">
					<RichText
						tagName="h4"
						className="event-details-title description-editable"
						placeholder={__('Audience', 'ctx-events')}
						value={description}
						onChange={(value) => {
							setAttributes({ description: value });
						}}
					/>
					<RichText
						tagName="span"
						className="event-details-data description-editable"
						placeholder={__('Enter audience', 'ctx-events')}
						value={audience}
						onChange={(value) => {
							setMeta({ _event_audience: value });
						}}
					/>
				</div>
			</div>
		</div>
	);
};

export default edit;
