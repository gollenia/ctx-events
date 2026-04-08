type EventIconProps = {
	name?: string | null;
	className?: string;
};

const getIconMap = (): Record<string, string> => {
	return window.ctxIcons ?? {};
};

export default function EventIcon({ name, className }: EventIconProps) {
	if (!name) {
		return null;
	}

	const markup = getIconMap()[name];

	if (!markup) {
		return null;
	}

	return (
		<span
			className={['ctx-events-icon', className].filter(Boolean).join(' ')}
			data-ctx-icon={name}
			aria-hidden="true"
			dangerouslySetInnerHTML={{ __html: markup }}
		/>
	);
}
