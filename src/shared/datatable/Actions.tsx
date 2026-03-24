import { useState } from '@wordpress/element';
import clsx from 'clsx';
import { useDataTable } from './DataTableContext';
import type { DataTableAction } from './types';

interface ActionProps {
	actions: Array<DataTableAction>;
	showOnHover?: boolean;
	item?: unknown;
}

const Actions = ({ actions, showOnHover, item }: ActionProps) => {
	const { onChangeView, view } = useDataTable();
	const [activeModalAction, setActiveModalAction] = useState<DataTableAction | null>(
		null,
	);

	const classes: string = clsx(
		'row-actions',
		showOnHover ? 'show-on-hover' : 'visible',
	);

	const onActionPerformed = () => {
		onChangeView({ refreshKey: (view.refreshKey || 0) + 1 });
	};

	const filteredActions = actions.filter((action) => {
		return action.disabled instanceof Function
			? !action.disabled(item)
			: !action.disabled;
	});

	const ActiveModal = activeModalAction?.RenderModal ?? null;

	return (
		<>
			<div className={classes}>
				{filteredActions.map((action: DataTableAction, index: number) => {
					const actionClasses: string = clsx({
						trash:
							action.delete instanceof Function
								? action.delete(item)
								: action.delete,
						last: index === filteredActions.length - 1,
					});

					if (
						action.disabled instanceof Function
							? action.disabled(item)
							: action.disabled
					) {
						return null;
					}
					return (
						<span className={actionClasses} key={index}>
							<a
								href="#"
								onClick={(e) => {
									e.preventDefault();
									if (action.RenderModal) {
										setActiveModalAction(action);
										return;
									}

									action.callback([item], onActionPerformed);
								}}
							>
								{action.label instanceof Function
									? action.label(item)
									: action.label}
							</a>
							{index < filteredActions.length - 1 && '\u00A0|\u00A0'}
						</span>
					);
				})}
			</div>
			{ActiveModal && item ? (
				<ActiveModal
					action={activeModalAction}
					item={item}
					onActionPerformed={onActionPerformed}
					onClose={() => setActiveModalAction(null)}
				/>
			) : null}
		</>
	);
};

export default Actions;
export type { ActionProps };
