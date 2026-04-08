import type {
	CSSProperties,
	ComponentPropsWithoutRef,
	ElementType,
	PropsWithChildren,
} from 'react';

export type FlexDirection =
	| 'row'
	| 'row-reverse'
	| 'column'
	| 'column-reverse';

export type FlexAlign =
	| 'stretch'
	| 'flex-start'
	| 'center'
	| 'flex-end'
	| 'baseline';

export type FlexJustify =
	| 'flex-start'
	| 'center'
	| 'flex-end'
	| 'space-between'
	| 'space-around'
	| 'space-evenly';

export type FlexWrap = 'nowrap' | 'wrap' | 'wrap-reverse';

type FlexOwnProps<T extends ElementType> = {
	as?: T;
	direction?: FlexDirection;
	align?: FlexAlign;
	justify?: FlexJustify;
	wrap?: FlexWrap;
	gap?: CSSProperties['gap'];
	inline?: boolean;
	style?: CSSProperties;
};

export type FlexProps<T extends ElementType = 'div'> = PropsWithChildren<
	FlexOwnProps<T> &
		Omit<ComponentPropsWithoutRef<T>, keyof FlexOwnProps<T> | 'children'>
>;

export function Flex<T extends ElementType = 'div'>({
	as,
	children,
	direction = 'row',
	align = 'stretch',
	justify = 'flex-start',
	wrap = 'nowrap',
	gap,
	inline = false,
	style,
	...props
}: FlexProps<T>) {
	const Component = (as ?? 'div') as ElementType;

	return (
		<Component
			{...props}
			style={{
				display: inline ? 'inline-flex' : 'flex',
				flexDirection: direction,
				alignItems: align,
				justifyContent: justify,
				flexWrap: wrap,
				gap,
				...style,
			}}
		>
			{children}
		</Component>
	);
}

export default Flex;
