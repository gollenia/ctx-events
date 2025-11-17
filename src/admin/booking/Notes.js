import { __ } from '@wordpress/i18n';

const Notes = ({ store }) => {
	const [state, dispatch] = store;
	const { booking } = state.data;
	return (
		<div className="booking-notes">
			<h2>{__('Notes', 'events')}</h2>
			{booking.notes && booking.notes.length > 0 ? (
				<ul>
					{booking.notes.map((note, index) => (
						<li key={index}>
							<strong>{note.date}:</strong> {note.text}
						</li>
					))}
				</ul>
			) : (
				<p>{__('No notes available for this booking.', 'events')}</p>
			)}
			<p>{__('Add a note to this booking:', 'events')}</p>
			<textarea
				placeholder={__('Type your note here...', 'events')}
				rows="4"
				style={{ width: '100%' }}
				onChange={(e) => {
					// Handle note input change
					console.log('Note input changed:', e.target.value);
				}}
			></textarea>
			<button
				onClick={() => {
					// Handle note submission

					dispatch({
						type: 'ADD_NOTE',
						payload: {
							date: new Date().toLocaleString(),
							text: document.querySelector('.booking-notes textarea').value,
						},
					});
				}}
				style={{ marginTop: '10px' }}
			>
				{__('Add Note', 'events')}
			</button>
		</div>
	);
};

export default Notes;
