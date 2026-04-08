import { classNames } from '../utilities/classNames';

type PlusProps = {
	className?: string;
};

export default function Plus({ className }: PlusProps) {
	return (
		<span className={classNames('ctx-plus-icon', className)} aria-hidden="true">
			<svg viewBox="0 0 16 16" focusable="false">
				<path
					d="M8 3.5v9M3.5 8h9"
					fill="none"
					stroke="currentColor"
					strokeLinecap="round"
					strokeWidth="1.75"
				/>
			</svg>
		</span>
	);
}
