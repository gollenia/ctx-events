import { Accordion } from '@base-ui/react/accordion';
import { classNames } from '@events/utilities';
import type { ReactNode } from 'react';
import { CheckIcon } from './components/CheckIcon';
import { SectionHeading } from './components/SectionHeading';
import type { SectionId } from './types';

type Props = {
	id: SectionId;
	title: string;
	isCompleted: boolean;
	isDisabled?: boolean;
	children: ReactNode;
};

export function AccordionSection({
	id,
	title,
	isCompleted,
	isDisabled = false,
	children,
}: Props) {
	return (
		<Accordion.Item
			value={id}
			disabled={isDisabled}
			className={(state) =>
				classNames(
					'booking-accordion__section',
					state.open && 'booking-accordion__section--open',
					isCompleted && 'booking-accordion__section--completed',
					isDisabled && 'booking-accordion__section--disabled',
				)
			}
		>
			<Accordion.Header
				className={(state) =>
					classNames(
						'booking-accordion__header-wrap',
						state.open && 'booking-accordion__header-wrap--open',
					)
				}
			>
				<Accordion.Trigger
					className="booking-accordion__header"
					disabled={isDisabled}
				>
					{isCompleted && <CheckIcon />}
					<span className="booking-accordion__title">{title}</span>
				</Accordion.Trigger>
			</Accordion.Header>
			<Accordion.Panel className="booking-accordion__content">
				<div className="booking-accordion__content-inner">
					<SectionHeading title={title} />
					{children}
				</div>
			</Accordion.Panel>
		</Accordion.Item>
	);
}
