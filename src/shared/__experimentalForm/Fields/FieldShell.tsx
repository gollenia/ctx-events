import type { ReactNode } from 'react';

type FieldMessagesProps = {
	hint?: string;
	hintId?: string;
	help?: string;
	helpHtml?: string;
	helpId?: string;
	errorMessage?: string;
	errorId: string;
	hasError?: boolean;
};

type FieldShellProps = FieldMessagesProps & {
	className: string;
	label?: string;
	required?: boolean;
	labelFor?: string;
	labelId?: string;
	children: ReactNode;
};

export const FieldMessages = ({
	hint,
	hintId,
	help,
	helpHtml,
	helpId,
	errorMessage,
	errorId,
	hasError = false,
}: FieldMessagesProps) => (
	<>
		{hint && hintId && (
			<div id={hintId} className="ctx-form-hint">
				{hint}
			</div>
		)}

		{helpHtml && helpId && (
			<div
				id={helpId}
				className="ctx-form-help"
				dangerouslySetInnerHTML={{ __html: helpHtml }}
			/>
		)}

		{help && helpId && !helpHtml && (
			<div id={helpId} className="ctx-form-help">
				{help}
			</div>
		)}

		{hasError && errorMessage && (
			<div id={errorId} role="alert" className="error-message">
				{errorMessage}
			</div>
		)}
	</>
);

const FieldShell = ({
	className,
	label,
	required = false,
	labelFor,
	labelId,
	hint,
	hintId,
	help,
	helpId,
	errorMessage,
	errorId,
	hasError = false,
	children,
}: FieldShellProps) => {
	return (
		<div className={className}>
			{label && (
				<label id={labelId} htmlFor={labelFor} className="ctx-form-label">
					<span className="ctx-form-label__text">{label}</span>
					{required && (
						<span
							className="ctx-form-label__required"
							aria-hidden="true"
						/>
					)}
				</label>
			)}

			{children}

			<FieldMessages
				hint={hint}
				hintId={hintId}
				help={help}
				helpId={helpId}
				errorMessage={errorMessage}
				errorId={errorId}
				hasError={hasError}
			/>
		</div>
	);
};

export default FieldShell;
