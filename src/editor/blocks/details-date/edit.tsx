import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { formatDateRange } from '@events/i18n';
import type {
	DetailsDateAttributes,
	DetailBlockProps,
	EventDateMeta,
} from '@events/details/types';
import EventIcon from '../../../shared/icons/EventIcon';
import Inspector from './inspector';

const edit = (props: DetailBlockProps<DetailsDateAttributes>) => {
	const postType = (select('core/editor') as { getCurrentPostType: () => string })
		.getCurrentPostType();

	if (postType !== 'ctx-event') {
		return null;
	}

	const [meta, setMeta] = useEntityProp('postType', postType, 'meta') as [
		EventDateMeta,
		(value: EventDateMeta) => void,
	];
	const {
		attributes: { description },
		setAttributes,
	} = props;

	const blockProps = useBlockProps();

	const startFormatted = () => {
		if (!meta?._event_start || !meta?._event_end) {
			return '';
		}

		return formatDateRange(meta._event_start, meta._event_end);
	};

	return (
		<div {...blockProps}>
			<Inspector {...props} meta={meta} setMeta={setMeta} />

			<div className="event-details-item">
				<div className="event-details-image">
					<EventIcon name="date" />
				</div>
				<div className="event-details-text">
					<RichText
						tagName="h4"
						className="event-details-title description-editable"
						placeholder={__('Date', 'ctx-events')}
						value={description}
						onChange={(value) => {
							setAttributes({ description: value });
						}}
					/>
					<span className="event-details-data description-editable">
						{startFormatted()}
					</span>
				</div>
			</div>
		</div>
	);
};

export default edit;
