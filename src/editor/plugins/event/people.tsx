import {
	ComboboxControl,
	Flex,
	Icon,
	TextControl,
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

import icons from './icons';
import './speaker.scss';
import type { ComboboxControlOption } from '@wordpress/components/build-types/combobox-control/types';
import type { EditorSelection, EventMeta, MediaOption } from './types';

type PersonRecord = {
	id: number;
	title?: {
		raw?: string;
	};
	meta?: {
		thumbnail?: string;
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

const PeopleSelector = () => {
	const postType = useSelect((select) => {
		const editor = select('core/editor') as EditorSelection;
		return editor.getCurrentPostType() ?? '';
	}, []);

	const [rawMeta, setMeta] = useEntityProp('postType', postType, 'meta');
	const meta = (rawMeta ?? {}) as EventMeta;

	const personList = useSelect((select) => {
		const { getEntityRecords } = select(coreStore);
		const list = (getEntityRecords('postType', 'ctx-event-person', {
			per_page: -1,
			_embed: true,
		}) ?? []) as PersonRecord[];

		return list.map(
			(person): ComboboxControlOption => ({
				key: person.id,
				value: String(person.id),
				label: person.title?.raw ?? '',
				media:
					person._embedded?.['wp:featuredmedia']?.[0]?.media_details?.sizes
						?.thumbnail?.source_url,
			}),
		) as ComboboxControlOption[];
	}, []);

	if (postType !== 'ctx-event') {
		return null;
	}

	return (
		<PluginDocumentSettingPanel
			name="events-people-settings"
			title={
				<Flex align="center" gap="0.5rem" justify="flex-start">
					<Icon
						icon={icons.person}
						width={20}
						height={20}
						color="rgb(117, 117, 117)"
					/>
					{__('People', 'ctx-events')}
				</Flex>
			}
			className="events-people-settings"
		>
			<VStack>
				<ComboboxControl
					label={__('Select a person', 'ctx-events')}
					value={String(meta._person_id ?? '')}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					onChange={(value) => {
						setMeta({ _person_id: Number(value || 0) });
					}}
					options={personList}
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
										icon={icons.person}
										height={16}
										width={16}
									/>
								)}
								{item.label}
							</div>
						);
					}}
				/>

				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label={__('Audience', 'ctx-events')}
					value={meta._event_audience ?? ''}
					onChange={(value) => {
						setMeta({ _event_audience: value });
					}}
				/>
			</VStack>
		</PluginDocumentSettingPanel>
	);
};

export default PeopleSelector;
