import { Flex, Modal } from '@wordpress/components';
import { ENTER } from '@wordpress/keycodes';
import type { ReactNode } from '@wordpress/element';

type Props = {
	title: ReactNode;
	onClose: () => void;
	isBusy?: boolean;
	children: ReactNode;
	footer?: ReactNode;
	className?: string;
	onConfirm?: () => void;
};

const ActionModal = ({
	title,
	onClose,
	isBusy = false,
	children,
	footer,
	className = 'ctx-action-modal',
	onConfirm,
}: Props) => {
	return (
		<Modal
			title={title}
			onRequestClose={isBusy ? undefined : onClose}
			shouldCloseOnClickOutside={!isBusy}
			className={className}
			onKeyDown={(event) => {
				if (
					event.keyCode !== ENTER ||
					isBusy ||
					!onConfirm ||
					event.defaultPrevented
				) {
					return;
				}

				const target = event.target;
				if (
					target instanceof HTMLElement &&
					(target.tagName === 'TEXTAREA' || target.isContentEditable)
				) {
					return;
				}

				event.preventDefault();
				onConfirm();
			}}
		>
			<Flex direction="column" gap="1rem">
				<Flex direction="column" gap="1rem" className="ctx-action-modal__body">
					{children}
				</Flex>

				{footer ? (
					<Flex
						justify="flex-end"
						gap="1rem"
						className="ctx-action-modal__footer"
					>
						{footer}
					</Flex>
				) : null}
			</Flex>
		</Modal>
	);
};

export default ActionModal;
