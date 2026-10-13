import type { ReactNode } from 'react';

export type StatusPillState = 'error' | 'warning' | 'info' | 'success';

type StatusPillProps = {
	state: StatusPillState;
	children: ReactNode;
};

const StatusPill = ({ state, children }: StatusPillProps) => (
	<span className={`ctx-status-pill ctx-status-pill--${state}`}>
		{children}
	</span>
);

export default StatusPill;
export type { StatusPillProps };
