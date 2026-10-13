import { useState } from '@wordpress/element';
import type { CSSProperties, ReactNode } from 'react';
import './style-index.scss';

export type SmallDataTableField<T> = {
	id: string;
	label?: ReactNode;
	ariaLabel?: string;
	headerClassName?: string;
	headerStyle?: CSSProperties;
	cellClassName?: string | ((item: T, index: number) => string | undefined);
	cellStyle?:
		| CSSProperties
		| ((item: T, index: number) => CSSProperties | undefined);
	render: (item: T, index: number) => ReactNode;
};

type SmallDataTableProps<T> = {
	items: T[];
	fields: SmallDataTableField<T>[];
	getRowKey: (item: T, index: number) => string | number;
	rowClassName?: (item: T, index: number) => string | undefined;
	draggable?: boolean;
	isDraggable?: (item: T, index: number) => boolean;
	onReorder?: (items: T[]) => void;
	className?: string;
	emptyMessage?: ReactNode;
	emptyState?: ReactNode;
	showHeader?: boolean;
};

/**
 * A lightweight table shell for small, local data sets such as modal editors.
 * Field renderers keep data-specific content and actions close to their caller.
 */
const SmallDataTable = <T,>({
	items,
	fields,
	getRowKey,
	rowClassName,
	draggable = false,
	isDraggable,
	onReorder,
	className = 'widefat striped',
	emptyMessage,
	emptyState,
	showHeader = true,
}: SmallDataTableProps<T>) => {
	const [draggedIndex, setDraggedIndex] = useState<number | null>(null);
	const [dropTargetIndex, setDropTargetIndex] = useState<number | null>(null);
	const canReorder = draggable && Boolean(onReorder);
	const rowCanBeDragged = (item: T, index: number) =>
		canReorder && (isDraggable?.(item, index) ?? true);
	const reorder = (targetIndex: number) => {
		if (draggedIndex === null || !onReorder) return;

		const reorderedItems = [...items];
		const [draggedItem] = reorderedItems.splice(draggedIndex, 1);
		if (!draggedItem) return;
		const insertionIndex =
			targetIndex > draggedIndex ? targetIndex - 1 : targetIndex;
		if (insertionIndex === draggedIndex) return;
		reorderedItems.splice(insertionIndex, 0, draggedItem);
		onReorder(reorderedItems);
	};
	const setDropTargetFromRow = (
		event: React.DragEvent<HTMLTableRowElement>,
		index: number,
	) => {
		const { top, height } = event.currentTarget.getBoundingClientRect();
		setDropTargetIndex(event.clientY < top + height / 2 ? index : index + 1);
	};

	if (items.length === 0 && emptyState) {
		return <>{emptyState}</>;
	}

	return (
		<table className={className}>
			{showHeader && (
				<thead>
					<tr>
						{fields.map(
							({ id, label, ariaLabel, headerClassName, headerStyle }) => (
								<th
									key={id}
									className={headerClassName}
									style={headerStyle}
									aria-label={ariaLabel}
									scope="col"
								>
									{label}
								</th>
							),
						)}
					</tr>
				</thead>
			)}
			<tbody>
				{items.length === 0 && emptyMessage ? (
					<tr>
						<td colSpan={fields.length}>{emptyMessage}</td>
					</tr>
				) : (
					items.map((item, index) => {
						const rowIsDraggable = rowCanBeDragged(item, index);
						const rowClasses = [
							rowClassName?.(item, index),
							draggedIndex === index && 'ctx-small-data-table__row--dragging',
							dropTargetIndex === index &&
								'ctx-small-data-table__row--drop-before',
							index === items.length - 1 &&
								dropTargetIndex === items.length &&
								'ctx-small-data-table__row--drop-after',
						]
							.filter(Boolean)
							.join(' ');
						return (
							<tr
								key={getRowKey(item, index)}
								className={rowClasses}
								draggable={rowIsDraggable}
								onDragStart={(event) => {
									if (!rowIsDraggable) return;
									event.dataTransfer.effectAllowed = 'move';
									event.dataTransfer.setData('text/plain', String(index));
									setDraggedIndex(index);
								}}
								onDragOver={(event) => {
									if (rowIsDraggable && draggedIndex !== null) {
										event.preventDefault();
										setDropTargetFromRow(event, index);
									}
								}}
								onDrop={(event) => {
									event.preventDefault();
									const { top, height } =
										event.currentTarget.getBoundingClientRect();
									reorder(event.clientY < top + height / 2 ? index : index + 1);
									setDraggedIndex(null);
									setDropTargetIndex(null);
								}}
								onDragEnd={() => {
									setDraggedIndex(null);
									setDropTargetIndex(null);
								}}
							>
								{fields.map((field) => (
									<td
										key={field.id}
										className={
											typeof field.cellClassName === 'function'
												? field.cellClassName(item, index)
												: field.cellClassName
										}
										style={
											typeof field.cellStyle === 'function'
												? field.cellStyle(item, index)
												: field.cellStyle
										}
									>
										{field.render(item, index)}
									</td>
								))}
							</tr>
						);
					})
				)}
			</tbody>
		</table>
	);
};

export default SmallDataTable;
export type { SmallDataTableProps };
