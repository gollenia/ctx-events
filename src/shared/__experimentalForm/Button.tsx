import type { ButtonHTMLAttributes, PropsWithChildren } from 'react';

type ButtonVariant = 'primary' | 'secondary' | 'tertiary';

type Props = PropsWithChildren<
	ButtonHTMLAttributes<HTMLButtonElement> & {
		variant?: ButtonVariant;
		fullWidth?: boolean;
	}
>;

export default function Button({
	children,
	className = '',
	variant = 'primary',
	fullWidth = false,
	type = 'button',
	...props
}: Props) {
	const classes = [
		'ctx-button',
		`ctx-button--${variant}`,
		fullWidth ? 'ctx-button--full-width' : '',
		className,
	]
		.filter(Boolean)
		.join(' ');

	return (
		<button type={type} className={classes} {...props}>
			{children}
		</button>
	);
}
