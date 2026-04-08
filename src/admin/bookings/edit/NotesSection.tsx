import {
	Button,
	Notice,
	Panel,
	PanelBody,
	TextareaControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { BookingDetail } from 'src/types/types';

type Props = {
	booking: BookingDetail;
	isSaving: boolean;
	onAdd: (text: string) => Promise<void>;
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
		} catch (err: any) {
			setError(err?.message ?? __('Could not save note.', 'ctx-events'));
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
					<ul className="booking-edit__notes">
						{booking.notes.map((note, index) => (
							<li key={index} className="booking-edit__note">
								<span className="booking-edit__note-date">
									{new Date(note.date).toLocaleString()}
								</span>
								{note.author && (
									<span className="booking-edit__note-author">
										{note.author}
									</span>
								)}
								<p>{note.text}</p>
							</li>
						))}
					</ul>
				)}

				<TextareaControl
					label={__('Add note', 'ctx-events')}
					value={text}
					onChange={setText}
					rows={3}
					disabled={isSaving}
				/>
				<Button
					variant="secondary"
					onClick={addNote}
					disabled={!text.trim() || isSaving}
				>
					{isSaving
						? __('Saving…', 'ctx-events')
						: __('Add Note', 'ctx-events')}
				</Button>
			</PanelBody>
		</Panel>
	);
};

export default NotesSection;
