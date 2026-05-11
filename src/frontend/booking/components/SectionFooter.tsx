import { Flex } from '@contexis/wp-react-form';
import type { ReactNode } from 'react';

type Props = {
	children: ReactNode;
};

export function SectionFooter({ children }: Props) {
	return (
		<Flex className="booking-section__footer" justify="flex-end">
			{children}
		</Flex>
	);
}
