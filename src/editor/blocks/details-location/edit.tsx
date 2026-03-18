import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { select, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import type {
	DetailsLocationAttributes,
	DetailBlockProps,
	EventLocationMeta,
	LocationRecord,
} from '@events/details/types';
import Inspector from './inspector';

const edit = (props: DetailBlockProps<DetailsLocationAttributes>) => {
	const postType = (select('core/editor') as { getCurrentPostType: () => string })
		.getCurrentPostType();

	if (postType !== 'ctx-event') {
		return null;
	}

	const [meta] = useEntityProp('postType', postType, 'meta') as [
		EventLocationMeta,
	];
	const {
		attributes: {
			description,
			showAddress,
			showZip,
			showCity,
			showCountry,
			showTitle,
		},
		setAttributes,
	} = props;

	const location = useSelect(
		(selectFn) =>
			(selectFn('core') as {
				getEntityRecord: (
					kind: string,
					name: string,
					id?: number,
				) => LocationRecord | null;
			}).getEntityRecord('postType', 'location', meta._location_id),
		[meta._location_id],
	);

	const blockProps = useBlockProps({ className: 'event-details-item' });
	const hasPhoto =
		(blockProps.className ?? '').includes('is-style-photo') &&
		Boolean(
			location?._embedded?.['wp:featuredmedia']?.[0]?.media_details?.sizes
				?.thumbnail?.source_url,
		);

	return (
		<div {...blockProps}>
			<Inspector {...props} />

			<div className="event-details__item">
				<div className="event-details__icon">
					{hasPhoto ? (
						<img
							className="icon-round"
							src={
								location?._embedded?.['wp:featuredmedia']?.[0]?.media_details
									?.sizes?.thumbnail?.source_url
							}
							alt=""
						/>
					) : (
						<i className="material-icons material-symbols-outlined">place</i>
					)}
				</div>
				<div>
					<RichText
						tagName="h4"
						className="event-details_title description-editable"
						placeholder={__('Location', 'ctx-events')}
						value={description}
						onChange={(value) => {
							setAttributes({ description: value });
						}}
					/>
					{location ? (
						<div className="event-details_audience description-editable">
							{showTitle && <div>{location.title?.rendered}</div>}
							{location.meta?._location_address && showAddress && (
								<div>{location.meta._location_address}</div>
							)}
							<div>
								{location.meta?._location_postcode && showZip && (
									<span>{location.meta._location_postcode} </span>
								)}
								{location.meta?._location_town && showCity && (
									<span>{location.meta._location_town}</span>
								)}
							</div>
							{location.meta?._location_country && showCountry && (
								<div>{location.meta._location_country}</div>
							)}
						</div>
					) : (
						<div className="event-details_audience description-editable">
							{showTitle && <div>{__('No location selected', 'ctx-events')}</div>}
						</div>
					)}
				</div>
			</div>
		</div>
	);
};

export default edit;
