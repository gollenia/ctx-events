import { ComboboxControl, Icon, TextControl } from '@wordpress/components';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

import icons from './icons';
import './speaker.scss';
import type { EditorSelection, EventMeta, MediaOption } from './types';

type SpeakerRecord = {
	id: number;
	title?: {
		raw?: string;
	};
	meta?: {
		thumbnail?: string;
	};
};

const PeopleSelector = () => {
	const postType = useSelect((select) => {
		const editor = select('core/editor') as EditorSelection;
		return editor.getCurrentPostType() ?? '';
	}, []);

	const [rawMeta, setMeta] = useEntityProp('postType', postType, 'meta');
	const meta = (rawMeta ?? {}) as EventMeta;

	const speakerList = useSelect((select) => {
		const { getEntityRecords } = select(coreStore);
		const list = (getEntityRecords('postType', 'event-speaker', {
			per_page: -1,
			_embedded: true,
		}) ?? []) as SpeakerRecord[];

		return list.map((speaker) => ({
			value: speaker.id,
			label: speaker.title?.raw ?? '',
			media: speaker.meta?.thumbnail,
		})) as MediaOption[];
	}, []);

	if (postType !== 'ctx-event') {
		return null;
	}

	return (
		<PluginDocumentSettingPanel
			name="events-location-settings"
			title={__('Persons', 'ctx-events')}
			className="events-location-settings"
		>
			<ComboboxControl
				label={__('Select a speaker', 'ctx-events')}
				value={String(meta._speaker_id ?? '')}
				onChange={(value) => {
					setMeta({ _speaker_id: Number(value || 0) });
				}}
				options={speakerList}
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
				label={__('Audience', 'ctx-events')}
				value={meta._event_audience ?? ''}
				onChange={(value) => {
					setMeta({ _event_audience: value });
				}}
			/>
		</PluginDocumentSettingPanel>
	);
};

export default PeopleSelector;
