/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import { formatPrice } from '@events/i18n';
import Inspector from './inspector.js';

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const { roundImage, format, description, overwritePrice } = props.attributes;
	const { postType, postId } = props.context;
	const setAttributes = props.setAttributes;

	if (postType !== 'ctx-event') return null;

	const blockProps = useBlockProps({ className: 'event-details-item' });

	const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
	const tickets = meta._event_tickets;

	const getPrice = () => {
		if (tickets && tickets.length > 0) {
			const ticket = tickets[0];

			if (ticket.ticket_price) {
				return formatPrice(
					ticket.ticket_price,
					window.eventBlocksLocalization.currency,
				);
			}
			return __('Free', 'events');
		}
		return __('Free', 'events');
	};

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<div className="event-details__item">
				<div className="event-details__icon">
					<i className="material-icons material-symbols-outlined">savings</i>
				</div>
				<div>
					<RichText
						tagName="h4"
						className="event-details_title description-editable"
						placeholder={__('Price', 'events')}
						value={description}
						onChange={(value) => {
							setAttributes({ description: value });
						}}
					/>
					<RichText
						tagName="span"
						className="event-details_audience description-editable"
						placeholder={getPrice()}
						value={overwritePrice}
						onChange={(value) => {
							setAttributes({ overwritePrice: value });
						}}
					/>
				</div>
			</div>
		</div>
	);
};

export default edit;
