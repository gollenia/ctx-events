import type { ReactNode } from '@wordpress/element';
import type { SectionId } from './types';

type Props = {
	id: SectionId;
	title: string;
	isOpen: boolean;
	isCompleted: boolean;
	isDisabled?: boolean;
	onToggle: (id: SectionId) => void;
	children: ReactNode;
};

export function AccordionSection({
	id,
	title,
	isOpen,
	isCompleted,
	isDisabled = false,
	onToggle,
	children,
}: Props) {
	return (
		<div
			className={[
				'booking-accordion__section',
				isOpen ? 'booking-accordion__section--open' : '',
				isCompleted ? 'booking-accordion__section--completed' : '',
				isDisabled ? 'booking-accordion__section--disabled' : '',
			]
				.filter(Boolean)
				.join(' ')}
		>
			<button
				type="button"
				className="booking-accordion__header"
				onClick={() => !isDisabled && onToggle(id)}
				aria-expanded={isOpen}
				disabled={isDisabled}
			>
				<span className="booking-accordion__title">{title}</span>
				{isCompleted && (
					<span className="booking-accordion__check" aria-hidden="true">
						✓
					</span>
				)}
				<span className="booking-accordion__chevron" aria-hidden="true">
					{isOpen ? '▲' : '▼'}
				</span>
			</button>
			{isOpen && <div className="booking-accordion__content">{children}</div>}
		</div>
	);
}
