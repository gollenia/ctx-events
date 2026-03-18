import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityRecord } from '@wordpress/core-data';
import { __, _n, sprintf } from '@wordpress/i18n';
import type {
	DetailsSpacesAttributes,
	DetailBlockContext,
	DetailBlockProps,
	SpacesRecord,
} from '@events/details/types';
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

	const { record } = useEntityRecord(
		'postType',
		postType,
		postId,
	) as { record?: SpacesRecord };
	const spaces = record?.extras?.spaces || 0;
	const blockProps = useBlockProps({ className: 'event-details-item' });

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<div className="event-details__item">
				<div className="event-details__icon">
					<i className="material-icons material-symbols-outlined">
						{spaces === 0
							? 'sentiment_dissatisfied'
							: spaces > warningThreshold
								? 'groups'
								: 'report_problem'}
					</i>
				</div>
				<div>
					<RichText
						tagName="h4"
						className="event-details_title description-editable"
						placeholder={__('Free Spaces', 'ctx-events')}
						value={description}
						onChange={(value) => {
							setAttributes({ description: value });
						}}
					/>
					<span className="event-details_audience description-editable">
						{showNumber && spaces > warningThreshold ? (
							spaces
						) : (
							<>
								{spaces <= warningThreshold && spaces > 0 && (
									<span className="event-details_warning">
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
									<span className="event-details_warning">
										{bookedUpText || __('Booked up', 'ctx-events')}
									</span>
								)}
								{spaces > warningThreshold && (
									<span className="event-details_ok">
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
