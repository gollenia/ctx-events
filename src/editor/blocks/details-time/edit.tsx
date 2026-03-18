import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { formatTimeRange } from '../../../shared/i18n/datetime';
import type {
	DetailsTimeAttributes,
	DetailBlockProps,
	EventDateMeta,
} from '@events/details/types';
import Inspector from './inspector';

const edit = (props: DetailBlockProps<DetailsTimeAttributes>) => {
	const postType = (select('core/editor') as { getCurrentPostType: () => string })
		.getCurrentPostType();

	if (postType !== 'ctx-event') {
		return null;
	}

	const {
		attributes: { description },
		setAttributes,
	} = props;
	const [meta] = useEntityProp('postType', postType, 'meta') as [EventDateMeta];
	const blockProps = useBlockProps({ className: 'event-details-item' });

	const timeFormatted = () => {
		if (!meta?._event_start || !meta?._event_end) {
			return '';
		}

		return formatTimeRange(meta._event_start, meta._event_end);
	};

	return (
		<div {...blockProps}>
			<Inspector />

			<div className="event-details__item">
				<div className="event-details__icon">
					<i className="material-icons material-symbols-outlined">schedule</i>
				</div>
				<div>
					<RichText
						tagName="h4"
						className="event-details_title description-editable"
						placeholder={__('Time', 'ctx-events')}
						value={description}
						onChange={(value) => {
							setAttributes({ description: value });
						}}
					/>
					<span className="event-details_audience description-editable">
						{timeFormatted()}
					</span>
				</div>
			</div>
		</div>
	);
};

export default edit;
