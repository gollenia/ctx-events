import type {
	DetailBlockContext,
	DetailBlockProps,
	DetailsSpacesAttributes,
	EventBookingRecord,
} from '@events/details/types';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import { __, _n, sprintf } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import EventIcon from '../../../shared/icons/EventIcon';
import Inspector from './inspector';

type SpacesBlockProps = DetailBlockProps<DetailsSpacesAttributes> & {
	context: DetailBlockContext;
};

const edit = (props: SpacesBlockProps) => {
	const {
		attributes: {
			description,
			showNumber,
			warningText,
			warningThreshold,
			okText,
			bookedUpText,
		},
		setAttributes,
		context: { postType, postId },
	} = props;

	if (postType !== 'ctx-event' || !postId) {
		return null;
	}

	const [spaces, setSpaces] = useState<number | null | undefined>(undefined);

	useEffect(() => {
		let isCurrent = true;
		setSpaces(undefined);

		apiFetch<EventBookingRecord>({
			path: `/events/v3/events/${postId}?include=bookings`,
		})
			.then((event) => {
				if (isCurrent) {
					setSpaces(event.bookingSummary?.available ?? null);
				}
			})
			.catch(() => {
				if (isCurrent) {
					setSpaces(null);
				}
			});

		return () => {
			isCurrent = false;
		};
	}, [postId]);

	const hasSpaces = typeof spaces === 'number';
	const blockProps = useBlockProps();

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<div className="event-details-item">
				<div className="event-details-image">
					<EventIcon
						name={
							!hasSpaces
								? 'spaces_available'
								: spaces === 0
								? 'spaces_full'
								: spaces > warningThreshold
									? 'spaces_available'
									: 'warning'
						}
					/>
				</div>
				<div className="event-details-text">
					<RichText
						tagName="h4"
						className="event-details-title description-editable"
						placeholder={__('Free Spaces', 'ctx-events')}
						value={description}
						onChange={(value) => {
							setAttributes({ description: value });
						}}
					/>
					<span className="event-details-data description-editable">
						{spaces === undefined ? (
							__('Loading available spaces…', 'ctx-events')
						) : !hasSpaces ? (
							__('Space information is not available', 'ctx-events')
						) : showNumber && spaces > warningThreshold ? (
							spaces
						) : (
							<>
								{spaces <= warningThreshold && spaces > 0 && (
									<span className="event-details-status event-details-status--warning">
										{warningText
											? sprintf(
													warningText,
													showNumber ? spaces : __('few', 'ctx-events'),
												)
											: sprintf(
													_n(
														'Only %s space left',
														'Only %s spaces left',
														showNumber ? spaces : __('few', 'ctx-events'),
														'events',
													),
													showNumber ? spaces : __('few', 'ctx-events'),
												)}
									</span>
								)}
								{spaces === 0 && (
									<span className="event-details-status event-details-status--warning">
										{bookedUpText || __('Booked up', 'ctx-events')}
									</span>
								)}
								{spaces > warningThreshold && (
									<span className="event-details-status event-details-status--ok">
										{okText || __('Enough free spaces left', 'ctx-events')}
									</span>
								)}
							</>
						)}
					</span>
				</div>
			</div>
		</div>
	);
};

export default edit;
