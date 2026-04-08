import type { ReactNode } from 'react';

type Props = {
	children: ReactNode;
};

export function SectionFooter({ children }: Props) {
	return <div className="booking-section__footer">{children}</div>;
}
