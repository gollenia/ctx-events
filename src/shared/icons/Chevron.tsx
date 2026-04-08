import { classNames } from '../utilities/classNames';

type ChevronProps = {
	className?: string;
	open?: boolean;
	direction?: 'down' | 'right';
};

export default function Chevron({
	className,
	open = false,
	direction = 'down',
}: ChevronProps) {
	return (
		<span
			className={classNames(
				'ctx-chevron',
				`ctx-chevron--${direction}`,
				open && 'ctx-chevron--open',
				className,
			)}
			aria-hidden="true"
		/>
	);
}
