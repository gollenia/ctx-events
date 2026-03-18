import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { formatPrice } from '@events/i18n';
import type {
	DetailsPriceAttributes,
	DetailBlockContext,
	DetailBlockProps,
	EventTicketsMeta,
} from '@events/details/types';
import Inspector from './inspector';

type PriceBlockProps = DetailBlockProps<DetailsPriceAttributes> & {
	context: DetailBlockContext;
};

const edit = (props: PriceBlockProps) => {
	const {
		attributes: { description, overwritePrice },
		context: { postType },
		setAttributes,
	} = props;

	if (postType !== 'ctx-event') {
		return null;
	}

	const blockProps = useBlockProps({ className: 'event-details-item' });
	const [meta] = useEntityProp('postType', postType, 'meta') as [
		EventTicketsMeta,
	];
	const tickets = meta._event_tickets;

	const getPrice = () => {
		if (tickets && tickets.length > 0) {
			const ticket = tickets[0];

			if (ticket.ticket_price) {
				return formatPrice(
					ticket.ticket_price,
					window.eventEditorLocalization.currency,
				);
			}
		}

		return __('Free', 'ctx-events');
	};

	return (
		<div {...blockProps}>
			<Inspector />

			<div className="event-details__item">
				<div className="event-details__icon">
					<i className="material-icons material-symbols-outlined">savings</i>
				</div>
				<div>
					<RichText
						tagName="h4"
						className="event-details_title description-editable"
						placeholder={__('Price', 'ctx-events')}
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
