import type { KeyboardEvent } from 'react';
import { useRef, useState } from '@wordpress/element';
import type { FieldValue } from '../types';

export type ComboboxProps = {
	label: string;
	placeholder: string;
	name: string;
	required: boolean;
	width: number;
	options: string[];
	hasEmptyOption?: boolean;
	help: string;
	hint: string;
	disabled: boolean;
	multiple: boolean;
	customError: string;
	customErrorMessage?: string;
	onChange: (value: FieldValue) => void;
	value?: string;
};

const Combobox = (props: ComboboxProps) => {
	const { onChange, options, help, disabled, placeholder, required, label, name, width } = props;

	const inputRef = useRef<HTMLInputElement>(null);
	const listId = `${name}-listbox`;
	const helpOptionId = `${name}-option-help`;

	const [inputField, setInputField] = useState<string>('');
	const [selection, setSelection] = useState<number>(-1);
	const [listSelect, setListSelect] = useState<number>(-1);
	const [isFocused, setIsFocused] = useState(false);

	const dropdownSelect = (value: string) => {
		const index = options.findIndex((option) => option === value);
		setSelection(index);
		setInputField('');
		setIsFocused(false);
		onChange(value);
	};

	const filteredOptions = (): string[] => {
		if (inputField.length === 0) return options;
		if (inputField.slice(-1) === '*') {
			return options.filter((option) =>
				option.toLowerCase().startsWith(inputField.slice(0, -1).toLowerCase()),
			);
		}
		return options.filter((option) =>
			option.toLowerCase().includes(inputField.toLowerCase()),
		);
	};

	const keyPress = (event: KeyboardEvent<HTMLDivElement>) => {
		const filtered = filteredOptions();
		if (event.key === 'ArrowDown') {
			setListSelect((prev) => Math.min(prev + 1, filtered.length - 1));
		}
		if (event.key === 'ArrowUp') {
			setListSelect((prev) => Math.max(prev - 1, -1));
		}
		if (event.key === 'Enter' && listSelect >= 0) {
			dropdownSelect(filtered[listSelect]);
			inputRef.current?.blur();
		}
		if (event.key === 'Escape') {
			setListSelect(-1);
			inputRef.current?.blur();
		}
	};

	const nullSelect = () => {
		setListSelect(-1);
		dropdownSelect('');
	};

	const filtered = filteredOptions();
	const isExpanded = isFocused && !disabled;
	const activeDescendant =
		listSelect >= 0 ? `${name}-option-${listSelect}` :
		(listSelect === -1 && help ? helpOptionId : undefined);

	const classes = [
		'ctx-form-field',
		'combobox',
		'input--width-' + width,
		required ? 'select--required' : '',
	].join(' ');

	return (
		<div
			style={{ gridColumn: `span ${width}` }}
			className={classes}
			onKeyDown={keyPress}
		>
			<label htmlFor={name}>{label}</label>
			<input
				ref={inputRef}
				id={name}
				name={name}
				type="text"
				role="combobox"
				aria-expanded={isExpanded}
				aria-haspopup="listbox"
				aria-autocomplete="list"
				aria-controls={listId}
				aria-activedescendant={isExpanded ? activeDescendant : undefined}
				aria-required={required}
				disabled={disabled}
				onMouseOver={() => setListSelect(-1)}
				onFocus={() => setIsFocused(true)}
				onBlur={() => {
					setIsFocused(false);
					setListSelect(-1);
				}}
				placeholder={selection !== -1 ? options[selection] : placeholder}
				value={inputField}
				onChange={(event) => setInputField(event.target.value)}
			/>
			<ul
				id={listId}
				role="listbox"
				aria-label={label}
				hidden={!isExpanded}
			>
				{help !== '' && (
					<li
						id={helpOptionId}
						role="option"
						aria-selected={selection === -1}
						className={listSelect === -1 ? 'selected' : ''}
						onMouseDown={nullSelect}
					>
						{help}
					</li>
				)}
				{filtered.map((option, index) => (
					<li
						key={option}
						id={`${name}-option-${index}`}
						role="option"
						aria-selected={selection === options.indexOf(option)}
						className={listSelect === index ? 'selected' : ''}
						onMouseDown={() => dropdownSelect(option)}
					>
						{option}
					</li>
				))}
				{filtered.length === 0 && (
					<li className="muted" role="presentation">
						No Result
					</li>
				)}
			</ul>
		</div>
	);
};

Combobox.defaultProps = {
	label: '',
	placeholder: '',
	name: '',
	options: [],
	required: false,
	width: 6,
};

export default Combobox;
