import React from '@wordpress/element';
import clsx from 'clsx';

type RowAction = {
	label: string,
	slug: string,
	callback: (slug: string) => void,
	delete?: boolean
};

type RowActionProps = {
	actions: Array<RowAction>,
	showOnHover?: boolean,
	className?: string
};

const RowActions = ({
	actions,
	showOnHover,
	className,
}: RowActionProps) => {
	
	const classes: string = clsx(
		'row-actions',
		showOnHover ? 'show-on-hover' : 'visible',
		className
	);

	return <div className={classes}>
		{actions.map((action: RowAction, index: number) => {
			const actionClasses: string = clsx({
				trash: action.delete,
				last: index === actions.length - 1,
			});
			return (

					<span className={actionClasses} key={index}>
						<a href="#" onClick={() => action.callback(action.slug)}>
							{action.label}
						</a>
						{index < actions.length - 1 && "\u00A0|\u00A0"}
					</span>
					

		    )
		})}
	</div>
}

export default RowActions;
export type { RowActionProps, RowAction };