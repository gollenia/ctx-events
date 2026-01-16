import clsx from 'clsx';

const RowActions = ({
	actions,
	showOnHover,
	className,
}) => {
	const classes = clsx(
		'row-actions',
		showOnHover ? 'show-on-hover' : 'visible',
		className
	);

	console.log(actions);
	return <div className={classes}>
		{actions.map((action, index) => {
			const actionClasses = clsx({
				trash: action.delete,
				last: index === actions.length - 1,
			});
			return (
				<span className={actionClasses}>
					<a key={index} href="#" onClick={() => action.callback(action.slug)}>
						{action.label}
					</a>
					{index < actions.length - 1 && " | "}
				</span>
		    )
		})}
	</div>
}

export default RowActions;
