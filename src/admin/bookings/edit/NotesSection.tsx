import { Flex } from '@contexis/wp-react-form';
import {
	Button,
	Notice,
	Panel,
	PanelBody,
	TextareaControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import hashToHue from '../../../shared/utilities/hashToHue';
import type { BookingDetail, BookingNoteResource } from '../../../types/types';

type Props = {
	booking: BookingDetail;
	isSaving: boolean;
	onAdd: (text: string) => Promise<void>;
};

const getAvatarLabel = (author: string) => {
	const trimmed = author.trim();
	if (!trimmed) return '?';

	const parts = trimmed.split(/\s+/).filter(Boolean);
	return parts
		.slice(0, 2)
		.map((part) => part.charAt(0).toUpperCase())
		.join('');
};

const NotesSection = ({ booking, isSaving, onAdd }: Props) => {
	const [text, setText] = useState('');
	const [error, setError] = useState<string | null>(null);

	const addNote = async () => {
		const trimmed = text.trim();
		if (!trimmed) return;

		setError(null);

		try {
			await onAdd(trimmed);
			setText('');
		} catch (error: any) {
			setError(error?.message ?? __('Could not save note.', 'ctx-events'));
		}
	};

	return (
		<Panel header={__('Notes', 'ctx-events')}>
			<PanelBody>
				{error && (
					<Notice status="error" isDismissible={false}>
						{error}
					</Notice>
				)}

				{booking.notes.length === 0 ? (
					<p className="booking-edit__empty">
						{__('No notes yet.', 'ctx-events')}
					</p>
				) : (
					<Flex direction="column" as="ul" gap="0.75rem">
						{booking.notes.map((note: BookingNoteResource, index: number) => (
							<li key={index} className="booking-edit__note">
								<div
									className="booking-edit__note-avatar"
									style={{
										backgroundColor: `hsl(${hashToHue(note.author || note.text)}, 55%, 90%)`,
										color: `hsl(${hashToHue(note.author || note.text)}, 45%, 28%)`,
									}}
									aria-hidden="true"
								>
									{getAvatarLabel(note.author)}
								</div>
								<div className="booking-edit__note-content">
									<div className="booking-edit__note-meta">
										<strong className="booking-edit__note-author">
											{note.author || __('Guest', 'ctx-events')}
										</strong>
										<span className="booking-edit__note-date">
											{new Date(note.date).toLocaleString()}
										</span>
									</div>
									<p className="booking-edit__note-text">{note.text}</p>
								</div>
							</li>
						))}
					</Flex>
				)}

				<TextareaControl
					label={__('Add note', 'ctx-events')}
					value={text}
					onChange={setText}
					rows={1}
					disabled={isSaving}
					style={{ marginBottom: '1em' }}
				/>
				<Flex
					align="flex-end"
					justify="flex-end"
					style={{ marginBottom: '1em' }}
				>
					<Button
						variant="secondary"
						onClick={addNote}
						disabled={!text.trim() || isSaving}
					>
						{isSaving
							? __('Saving…', 'ctx-events')
							: __('Add Note', 'ctx-events')}
					</Button>
				</Flex>
			</PanelBody>
		</Panel>
	);
};

export default NotesSection;
