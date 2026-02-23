import clsx from 'clsx';
import type { DataTableAction } from './types';

interface RowActionProps {
	actions: Array<DataTableAction>;
	showOnHover?: boolean;
}

const Actions = ({ actions, showOnHover }: RowActionProps) => {
	const classes: string = clsx(
		'row-actions',
		showOnHover ? 'show-on-hover' : 'visible',
	);

	return (
		<div className={classes}>
			{actions.map((action: DataTableAction, index: number) => {
				const actionClasses: string = clsx({
					trash: action.delete,
					last: index === actions.length - 1,
				});
				return (
					<span className={actionClasses} key={index}>
						<a
							href="#"
							onClick={(e) => {
								e.preventDefault();
								action.callback([action.id]);
							}}
						>
							{action.label instanceof Function ? action.label() : action.label}
						</a>
						{index < actions.length - 1 && '\u00A0|\u00A0'}
					</span>
				);
			})}
		</div>
	);
};

export default Actions;
export type { RowActionProps };
