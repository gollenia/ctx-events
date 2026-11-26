import { useEffect, useRef } from '@wordpress/element';

export type CodeMode = 'html' | 'css';
export type CodeEditorSettings = Partial<
	Record<CodeMode, Record<string, unknown>>
>;

type CodeMirror = {
	getValue(): string;
	setValue(value: string): void;
	getInputField(): HTMLElement;
	on(event: 'change', listener: () => void): void;
	off(event: 'change', listener: () => void): void;
	toTextArea(): void;
};

type CodeEditorWindow = Window & {
	wp?: {
		codeEditor?: {
			initialize(
				textarea: HTMLTextAreaElement,
				settings: Record<string, unknown>,
			): { codemirror: CodeMirror };
		};
	};
};

type Props = {
	id: string;
	label: string;
	description?: string;
	value: string;
	onChange: (value: string) => void;
	settings?: Record<string, unknown>;
};

export default function CodeField({
	id,
	label,
	description,
	value,
	onChange,
	settings,
}: Props) {
	const textareaRef = useRef<HTMLTextAreaElement>(null);
	const editorRef = useRef<CodeMirror | null>(null);
	const onChangeRef = useRef(onChange);
	const labelId = `${id}-label`;
	const descriptionId = description ? `${id}-help` : undefined;

	useEffect(() => {
		onChangeRef.current = onChange;
	}, [onChange]);

	useEffect(() => {
		const textarea = textareaRef.current;
		const api = (window as CodeEditorWindow).wp?.codeEditor;
		if (!textarea || !api || !settings) return;

		const { codemirror } = api.initialize(textarea, settings);
		editorRef.current = codemirror;
		const input = codemirror.getInputField();
		input.setAttribute('aria-labelledby', labelId);
		if (descriptionId) input.setAttribute('aria-describedby', descriptionId);

		const handleChange = () => onChangeRef.current(codemirror.getValue());
		codemirror.on('change', handleChange);
		return () => {
			codemirror.off('change', handleChange);
			codemirror.toTextArea();
			editorRef.current = null;
		};
	}, [settings, labelId, descriptionId]);

	useEffect(() => {
		const editor = editorRef.current;
		if (editor && editor.getValue() !== value) editor.setValue(value);
	}, [value]);

	return (
		<div className="components-base-control">
			<label id={labelId} htmlFor={id}>
				{label}
			</label>
			<textarea
				id={id}
				ref={textareaRef}
				rows={10}
				style={{ width: '100%' }}
				aria-describedby={descriptionId}
				value={value}
				onChange={(event) => onChange(event.currentTarget.value)}
			/>
			{description && (
				<p id={descriptionId} className="components-base-control__help">
					{description}
				</p>
			)}
		</div>
	);
}
