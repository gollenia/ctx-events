import { Flex, Icon, type IconType } from '@wordpress/components';

const PanelTitle = ({
	title,
	icon,
}: {
	title: string;
	icon: IconType | null | undefined;
}) => {
	return (
		<Flex align="center" gap="0.5rem" justify="flex-start">
			<Icon icon={icon} width={20} height={20} color="rgb(117, 117, 117)" />
			{title}
		</Flex>
	);
};

export default PanelTitle;
