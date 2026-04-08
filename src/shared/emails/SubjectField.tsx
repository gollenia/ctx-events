import { useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { KeyboardEvent, RefObject } from 'react';
import MentionPopover from './MentionPopover';
import type { EmailTemplateMentionItem } from './tiptap';

type Props = {
	value: string;
	inputRef: RefObject<HTMLInputElement | null>;
	commandOpen: boolean;
	items: EmailTemplateMentionItem[];
	selectedIndex: number;
	onChange: (value: string, selectionStart: number | null) => void;
	onCursorChange: (value: string, selectionStart: number | null) => void;
	onKeyDown: (event: KeyboardEvent<HTMLInputElement>) => void;
	onSelectItem: (item: EmailTemplateMentionItem) => void;
};

const SubjectField = ({
	value,
	inputRef,
	commandOpen,
	items,
	selectedIndex,
	onChange,
	onCursorChange,
	onKeyDown,
	onSelectItem,
}: Props) => {
	const fieldRef = useRef<HTMLDivElement | null>(null);

	return (
		<div className="ctx-email-editor__field" ref={fieldRef}>
			<label className="ctx-email-editor__label" htmlFor="ctx-email-subject">
				{__('Subject', 'ctx-events')}
			</label>
			<p className="ctx-email-editor__hint">
				{__('Type @ in the subject to insert tokens.', 'ctx-events')}
			</p>
			<input
				id="ctx-email-subject"
				ref={inputRef}
				className="ctx-email-editor__input"
				type="text"
				autoComplete="off"
				data-bwignore="true"
				data-lpignore="true"
				data-1p-ignore
				data-protonpass-ignore="true"
				value={value}
				onChange={(event) =>
					onChange(event.target.value, event.currentTarget.selectionStart)
				}
				onKeyDown={onKeyDown}
				onClick={(event) =>
					onCursorChange(
						event.currentTarget.value,
						event.currentTarget.selectionStart,
					)
				}
				onKeyUp={(event) =>
					onCursorChange(
						event.currentTarget.value,
						event.currentTarget.selectionStart,
					)
				}
			/>
			{commandOpen ? (
				<MentionPopover
					anchor={fieldRef.current}
					items={items}
					selectedIndex={selectedIndex}
					popoverClassName="ctx-email-editor__mentions-popover"
					onSelect={onSelectItem}
				/>
			) : null}
		</div>
	);
};

export default SubjectField;
