import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import type {
	DetailsAudienceAttributes,
	DetailBlockProps,
	EventAudienceMeta,
} from '@events/details/types';
import EventIcon from '../../../shared/icons/EventIcon';
import Inspector from './inspector';

const edit = (props: DetailBlockProps<DetailsAudienceAttributes>) => {
	const postType = (select('core/editor') as { getCurrentPostType: () => string })
		.getCurrentPostType();

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
	const blockProps = useBlockProps({ className: 'event-details-item' });

	return (
		<div {...blockProps}>
			<Inspector />

			<div className="event-details__item">
				<div className="event-details__icon">
					<EventIcon name="audience" />
				</div>
				<div>
					<RichText
						tagName="h4"
						className="event-details_title description-editable"
						placeholder={__('Audience', 'ctx-events')}
						value={description}
						onChange={(value) => {
							setAttributes({ description: value });
						}}
					/>
					<RichText
						tagName="span"
						className="event-details_audience description-editable"
						placeholder={__('Enter audience', 'ctx-events')}
						value={meta._event_audience ?? ''}
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
