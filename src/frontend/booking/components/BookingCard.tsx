import type { ReactNode } from 'react';

type Props = {
	children: ReactNode;
	className?: string;
	variant?: 'summary' | 'surface';
	as?: 'div' | 'section' | 'aside';
};

export function BookingCard({
	children,
	className = '',
	variant = 'surface',
	as: Component = 'div',
}: Props) {
	const classes = ['booking-card', `booking-card--${variant}`, className]
		.filter(Boolean)
		.join(' ');

	return <Component className={classes}>{children}</Component>;
}
