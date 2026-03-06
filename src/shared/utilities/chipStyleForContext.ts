import hashToHue from './hashToHue';

function chipStyleForContext(context?: string): React.CSSProperties {
	if (!context) return {};
	const hue = hashToHue(context.toLowerCase().trim());

	return {
		border: `1px solid hsl(${hue} 55% 45%)`,
		background: `hsl(${hue} 65% 92%)`,
		color: `hsl(${hue} 55% 25%)`,
	};
}

export default chipStyleForContext;
