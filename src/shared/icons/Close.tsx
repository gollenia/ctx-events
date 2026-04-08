import { classNames } from '../utilities/classNames';

type CloseProps = {
	className?: string;
};

export default function Close({ className }: CloseProps) {
	return (
		<span className={classNames('ctx-close-icon', className)} aria-hidden="true">
			<svg viewBox="0 0 16 16" focusable="false">
				<path
					d="M4 4l8 8M12 4l-8 8"
					fill="none"
					stroke="currentColor"
					strokeLinecap="round"
					strokeWidth="1.75"
				/>
			</svg>
		</span>
	);
}
