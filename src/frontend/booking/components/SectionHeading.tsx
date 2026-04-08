type Props = {
	title: string;
	as?: 'h2' | 'h3';
};

export function SectionHeading({ title, as: Heading = 'h2' }: Props) {
	return (
		<div className="booking-section-heading">
			<Heading className="booking-section-heading__title">{title}</Heading>
		</div>
	);
}
