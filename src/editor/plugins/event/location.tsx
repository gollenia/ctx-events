import { ComboboxControl, Flex, Icon } from '@wordpress/components';
import type { ComboboxControlOption } from '@wordpress/components/build-types/combobox-control/types';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import icons from './icons';
import type { EditorSelection, EventMeta, MediaOption } from './types';

type LocationRecord = {
	id: number;
	title?: {
		raw?: string;
	};
	_embedded?: {
		'wp:featuredmedia'?: Array<{
			media_details?: {
				sizes?: {
					thumbnail?: {
						source_url?: string;
					};
				};
			};
		}>;
	};
};

const LocationSelector = () => {
	const postType = useSelect((select) => {
		const editor = select('core/editor') as EditorSelection;
		return editor.getCurrentPostType() ?? '';
	}, []);

	const allowedPostTypes = ['ctx-event', 'event-recurring'];

	const [rawMeta, setMeta] = useEntityProp('postType', postType, 'meta');
	const meta = (rawMeta ?? {}) as EventMeta;

	const locationList = useSelect((select) => {
		const { getEntityRecords } = select(coreStore);
		const result = (getEntityRecords('postType', 'ctx-event-location', {
			per_page: -1,
			_embed: true,
		}) ?? []) as LocationRecord[];

		return result.map(
			(location): ComboboxControlOption => ({
				key: location.id,
				value: String(location.id),
				label: location.title?.raw ?? '',
				media:
					location._embedded?.['wp:featuredmedia']?.[0]?.media_details?.sizes
						?.thumbnail?.source_url,
			}),
		) as ComboboxControlOption[];
	}, []);

	if (!allowedPostTypes.includes(postType)) {
		return null;
	}

	return (
		<PluginDocumentSettingPanel
			name="events-location-settings"
			title={
				<Flex align="center" gap="0.5rem" justify="flex-start">
					<Icon
						icon={icons.location}
						width={20}
						height={20}
						color="rgb(117, 117, 117)"
					/>
					{__('Location', 'ctx-events')}
				</Flex>
			}
			className="events-location-settings"
		>
			<ComboboxControl
				label={__('Select a location', 'ctx-events')}
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				value={String(meta._location_id ?? '')}
				onChange={(value) => {
					setMeta({ _location_id: Number(value || 0) });
				}}
				options={locationList}
				allowReset={true}
				__experimentalRenderItem={({ item }: { item: MediaOption }) => {
					if (item.value === 0) {
						return null;
					}

					return (
						<div className="events-speaker-item">
							{item.media ? (
								<img
									className="icon-round"
									width="24"
									height="24"
									src={item.media}
									alt=""
								/>
							) : (
								<Icon
									className="icon-round"
									icon={icons.location}
									height={20}
									width={20}
								/>
							)}
							{item.label}
						</div>
					);
				}}
			/>
		</PluginDocumentSettingPanel>
	);
};

export default LocationSelector;
