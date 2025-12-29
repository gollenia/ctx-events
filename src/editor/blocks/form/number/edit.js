/**
 * Wordpress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { Icon, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import icons from './icons.js';
import Inspector from './inspector.js';
import lock from './lockIcon.js';

/**
 * @param {Props} props
 * @return {JSX.Element} Element
 */
const edit = (props) => {
	const {
		attributes: {
			width,
			required,
			placeholder,
			label,
			name,
			range,
			min,
			max,
			step,
		},
		setAttributes,
	} = props;

	const lockName = ['first_name', 'last_name'].includes(name);
	const validName = () => {
		const validPattern = /([a-zA-Z0-9_]){3,40}/;
		return validPattern.test(name);
	};

	const setName = (value) => {
		value = value.toLowerCase();
		value = value.replace(/\s/g, '-');
		setAttributes({ name: value.toLowerCase() });
	};

	const blockProps = useBlockProps({
		className: [
			'ctx:form-field',
			'ctx:form-field--' + width,
			validName() == false || label === '' ? 'ctx:form-field--error' : '',
		]
			.filter(Boolean)
			.join(' '),
	});

	return (
		<div {...blockProps}>
			<Inspector {...props} />
			<div className="ctx:form-field__caption">
				<div className="ctx:form-field__info">
					<Icon icon={icons.icon} />
					<div className="ctx:form-field__description">
						<span>
							<RichText
								tagName="span"
								className="ctx:form-details__label"
								value={label}
								placeholder={__('Label', 'events')}
								onChange={(value) => setAttributes({ label: value })}
							/>

							<span>{required ? '*' : ''}</span>
						</span>
						<span className="ctx:form-field__label">
							{__('Label for the field', 'events')}
						</span>
					</div>
				</div>

				<div className="ctx:form-field__name">
					{!lockName && (
						<RichText
							tagName="p"
							className="ctx:form-details__label"
							value={name}
							placeholder={__('Slug', 'events')}
							onChange={(value) => setName(value)}
						/>
					)}
					{lockName && (
						<span className="ctx:form-details__label--lock">
							{name} <Icon icon={lock} size={14} />
						</span>
					)}
					{validName() == false && (
						<span className="ctx:form-field__error-message">
							{__('Please type in a unique itentifier for the field', 'events')}
						</span>
					)}
					{validName() && (
						<span className="ctx:form-field__label">
							{__('Unique identifier', 'events')}
						</span>
					)}
				</div>
			</div>

			{range ? (
				<RangeControl
					value={placeholder}
					onChange={(value) => setAttributes({ placeholder: value })}
					min={min}
					max={max}
					step={step}
				/>
			) : (
				<input
					autocomplete="off"
					value={placeholder}
					type="number"
					onChange={(event) =>
						setAttributes({ placeholder: event.target.value })
					}
				/>
			)}
		</div>
	);
};

export default edit;
