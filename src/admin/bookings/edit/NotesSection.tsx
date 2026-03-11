import { Button, TextareaControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { BookingDetail, BookingNoteResource } from 'src/types/types';

type Props = {
	booking: BookingDetail;
	onChange: (notes: BookingNoteResource[]) => void;
};

const NotesSection = ({ booking, onChange }: Props) => {
	const [text, setText] = useState('');

	const addNote = () => {
		const trimmed = text.trim();
		if (!trimmed) return;

		const newNote: BookingNoteResource = {
			text: trimmed,
			date: new Date().toISOString(),
			author: '',
		};

		onChange([...booking.notes, newNote]);
		setText('');
	};

	return (
		<section className="booking-edit__section">
			<h3>{__('Notes', 'ctx-events')}</h3>

			{booking.notes.length === 0 ? (
				<p className="booking-edit__empty">{__('No notes yet.', 'ctx-events')}</p>
			) : (
				<ul className="booking-edit__notes">
					{booking.notes.map((note, index) => (
						<li key={index} className="booking-edit__note">
							<span className="booking-edit__note-date">
								{new Date(note.date).toLocaleString()}
							</span>
							{note.author && (
								<span className="booking-edit__note-author">{note.author}</span>
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
			/>
			<Button variant="secondary" onClick={addNote} disabled={!text.trim()}>
				{__('Add Note', 'ctx-events')}
			</Button>
		</section>
	);
};

export default NotesSection;
