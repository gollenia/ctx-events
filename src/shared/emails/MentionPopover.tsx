import { Button, Popover } from '@wordpress/components';

import type { EmailTemplateMentionItem } from './tiptap';

type Props = {
	anchor: Element | null;
	items: EmailTemplateMentionItem[];
	selectedIndex: number;
	popoverClassName?: string;
	onSelect: (item: EmailTemplateMentionItem) => void;
	onClose?: () => void;
};

const MentionPopover = ({
	anchor,
	items,
	selectedIndex,
	popoverClassName,
	onSelect,
	onClose,
}: Props) => {
	if (!anchor || !items.length) {
		return null;
	}

	return (
		<Popover
			anchor={anchor}
			placement="bottom-start"
			offset={8}
			focusOnMount={false}
			className={popoverClassName}
			onClose={onClose}
		>
			<div className="ctx-email-editor__mentions" role="listbox">
				{items.map((item, index) => (
					<Button
						key={item.id}
						variant={index === selectedIndex ? 'primary' : 'tertiary'}
						className="ctx-email-editor__mention-item"
						onClick={() => onSelect(item)}
					>
						{item.label}
					</Button>
				))}
			</div>
		</Popover>
	);
};

export default MentionPopover;
