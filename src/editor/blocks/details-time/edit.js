/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import { formatTimeRange } from '../../../shared/i18n/datetime';
import Inspector from './inspector.js';

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const postType = select('core/editor').getCurrentPostType();

	if (postType !== 'ctx-event') return null;

	const {
		attributes: { description },
		setAttributes,
	} = props;

	const [meta, setMeta] = useEntityProp('postType', postType, 'meta');

	const {
		attributes: { roundImage, format },
	} = props;

	const blockProps = useBlockProps({ className: 'event-details-item' });

	const timeFormatted = () => {
		if (!meta) return;
		const start = meta._event_start;
		const end = meta._event_end;
		return formatTimeRange(start, end);
	};

	return (
		<div {...blockProps}>
			<Inspector {...props} />

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
