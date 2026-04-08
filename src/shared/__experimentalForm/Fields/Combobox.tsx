import {
	useEffect,
	useId,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import type {
	ChangeEvent,
	FocusEvent,
	KeyboardEvent,
	MouseEvent,
	ReactNode,
} from 'react';
import FieldShell from './FieldShell';

type RenderOptionState = {
	highlighted: boolean;
	selected: boolean;
};

type ComboboxProps = {
	label?: string;
	name?: string;
	width?: number;
	options: string[];
	required?: boolean;
	disabled?: boolean;
	placeholder?: string;
	help?: string;
	error?: string;
	customErrorMessage?: string;
	formTouched?: boolean;
	value?: string;
	onChange: (value: string) => void;
	allowClear?: boolean;
	clearLabel?: string;
	noResultsLabel?: string;
	renderOption?: (option: string, state: RenderOptionState) => ReactNode;
};

const Combobox = (props: ComboboxProps) => {
	const {
		label = '',
		name = '',
		width = 6,
		options,
		required = false,
		disabled = false,
		placeholder = '',
		help,
		error,
		customErrorMessage,
		formTouched = false,
		value = '',
		onChange,
		allowClear = false,
		clearLabel = 'Clear selection',
		noResultsLabel = 'No results',
		renderOption,
	} = props;

	const reactId = useId();
	const inputId = name || `combobox-${reactId}`;
	const listboxId = `${inputId}-listbox`;
	const helpId = help ? `${inputId}-help` : undefined;
	const errorId = `${inputId}-error`;
	const labelId = label ? `${inputId}-label` : undefined;

	const wrapperRef = useRef<HTMLDivElement>(null);
	const inputRef = useRef<HTMLInputElement>(null);

	const [touched, setTouched] = useState(false);
	const [isOpen, setIsOpen] = useState(false);
	const [inputValue, setInputValue] = useState(value);
	const [highlightedIndex, setHighlightedIndex] = useState<number>(-1);

	useEffect(() => {
		setInputValue(value);

		// External updates such as browser autofill should sync the value
		// without forcing the suggestion list to stay open.
		if (!value || options.includes(value)) {
			setIsOpen(false);
			setHighlightedIndex(-1);
		}
	}, [options, value]);

	const filteredOptions = useMemo(() => {
		const query = inputValue.trim().toLowerCase();

		if (!query) {
			return options;
		}

		if (query.endsWith('*')) {
			const prefix = query.slice(0, -1);
			return options.filter((option) =>
				option.toLowerCase().startsWith(prefix),
			);
		}

		return options.filter((option) => option.toLowerCase().includes(query));
	}, [inputValue, options]);

	const clearEnabled = allowClear && inputValue.trim() !== '';
	const totalItems = filteredOptions.length + (clearEnabled ? 1 : 0);

	const hasError = !!error;
	const errorMessage = error ?? customErrorMessage;
	const describedBy =
		[helpId, hasError && errorMessage ? errorId : undefined]
			.filter(Boolean)
			.join(' ') || undefined;

	const classes = [
		'ctx-form-field',
		'combobox',
		required ? 'input--required' : '',
		hasError ? 'error' : '',
	]
		.filter(Boolean)
		.join(' ');

	const activeDescendant = isOpen && highlightedIndex >= 0
		? clearEnabled && highlightedIndex === 0
			? `${inputId}-option-clear`
			: `${inputId}-option-${clearEnabled ? highlightedIndex - 1 : highlightedIndex}`
		: undefined;

	const openList = () => {
		if (disabled) return;
		setIsOpen(true);
	};

	const closeList = () => {
		setIsOpen(false);
		setHighlightedIndex(-1);
	};

	const commitSelection = (selectedValue: string) => {
		setInputValue(selectedValue);
		onChange(selectedValue);
		closeList();
	};

	const clearSelection = () => {
		setInputValue('');
		onChange('');
		closeList();
	};

	const handleInputChange = (event: ChangeEvent<HTMLInputElement>) => {
		const nextValue = event.currentTarget.value;
		setInputValue(nextValue);
		setIsOpen(true);
		setHighlightedIndex(-1);
		onChange(nextValue);
	};

	const handleFocus = () => {
		openList();
	};

	const closeIfFocusLeft = () => {
		window.setTimeout(() => {
			if (!wrapperRef.current?.contains(document.activeElement)) {
				setTouched(true);
				closeList();
			}
		}, 0);
	};

	const handleBlur = (_event: FocusEvent<HTMLInputElement>) => {
		closeIfFocusLeft();
	};

	const handleWrapperBlur = () => {
		closeIfFocusLeft();
	};

	const handleKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
		if (disabled) return;

		switch (event.key) {
			case 'ArrowDown': {
				event.preventDefault();
				if (!isOpen) {
					openList();
				}
				setHighlightedIndex((prev) =>
					prev < 0 ? 0 : Math.min(prev + 1, totalItems - 1),
				);
				break;
			}

			case 'ArrowUp': {
				event.preventDefault();
				if (!isOpen) {
					openList();
				}
				setHighlightedIndex((prev) =>
					prev < 0 ? totalItems - 1 : Math.max(prev - 1, 0),
				);
				break;
			}

			case 'Home': {
				if (!isOpen || totalItems === 0) return;
				event.preventDefault();
				setHighlightedIndex(0);
				break;
			}

			case 'End': {
				if (!isOpen || totalItems === 0) return;
				event.preventDefault();
				setHighlightedIndex(totalItems - 1);
				break;
			}

			case 'Enter': {
				if (isOpen && highlightedIndex >= 0) {
					event.preventDefault();
					if (clearEnabled && highlightedIndex === 0) {
						clearSelection();
						break;
					}

					const optionIndex = clearEnabled ? highlightedIndex - 1 : highlightedIndex;
					if (filteredOptions[optionIndex]) {
						commitSelection(filteredOptions[optionIndex]);
					}
				}
				break;
			}

			case 'Escape': {
				if (isOpen) {
					event.preventDefault();
					closeList();
				}
				break;
			}
		}
	};

	const handleOptionMouseDown = (
		event: MouseEvent<HTMLLIElement>,
		selectedValue: string,
	) => {
		event.preventDefault();
		commitSelection(selectedValue);
		inputRef.current?.focus();
	};

	const handleClearMouseDown = (event: MouseEvent<HTMLLIElement>) => {
		event.preventDefault();
		clearSelection();
		inputRef.current?.focus();
	};

	return (
		<FieldShell
			className={classes}
			label={label}
			required={required}
			labelFor={inputId}
			labelId={labelId}
			help={help}
			helpId={helpId}
			errorMessage={errorMessage}
			errorId={errorId}
			hasError={hasError}
		>
			<div ref={wrapperRef} onBlur={handleWrapperBlur}>
			<input
				ref={inputRef}
				id={inputId}
				name={name || undefined}
				type="text"
				role="combobox"
				value={inputValue}
				placeholder={placeholder}
				required={required}
				disabled={disabled}
				autoComplete="off"
				aria-labelledby={labelId}
				aria-describedby={describedBy}
				aria-required={required || undefined}
				aria-invalid={hasError || undefined}
				aria-errormessage={hasError && errorMessage ? errorId : undefined}
				aria-expanded={isOpen}
				aria-controls={listboxId}
				aria-haspopup="listbox"
				aria-autocomplete="list"
				aria-activedescendant={activeDescendant}
				onChange={handleInputChange}
				onFocus={handleFocus}
				onBlur={handleBlur}
				onKeyDown={handleKeyDown}
			/>

			{isOpen && !disabled && (
				<ul id={listboxId} role="listbox" className="ctx-combobox-listbox">
					{clearEnabled && (
						<li
							role="option"
							tabIndex={-1}
							aria-selected={false}
							className={highlightedIndex === 0 ? 'selected' : ''}
							id={`${inputId}-option-clear`}
							onMouseEnter={() => setHighlightedIndex(0)}
							onMouseDown={handleClearMouseDown}
						>
							{clearLabel}
						</li>
					)}

					{filteredOptions.map((option, index) => (
						<li
							key={option}
							tabIndex={-1}
							id={`${inputId}-option-${index}`}
							role="option"
							aria-selected={inputValue === option}
							className={
								highlightedIndex === (clearEnabled ? index + 1 : index)
									? 'selected'
									: ''
							}
							onMouseEnter={() =>
								setHighlightedIndex(clearEnabled ? index + 1 : index)
							}
							onMouseDown={(event) => handleOptionMouseDown(event, option)}
						>
							{renderOption
								? renderOption(option, {
										highlighted:
											highlightedIndex === (clearEnabled ? index + 1 : index),
										selected: inputValue === option,
									})
								: option}
						</li>
					))}

					{filteredOptions.length === 0 && (
						<li className="muted" role="presentation">
							{noResultsLabel}
						</li>
					)}
				</ul>
			)}
			</div>
		</FieldShell>
	);
};

export default Combobox;
export type { ComboboxProps };
