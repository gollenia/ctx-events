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

	return (
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
	);
};

export default Actions;
export type { ActionProps };
