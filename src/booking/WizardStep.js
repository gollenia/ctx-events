import { useRef } from 'react';

const WizardStep = ( props ) => {
	const { children, isActive, index, currentStep, valid, nextButtonLabel, invalidMessage, enableFocusTrap } = props;

	const ref = useRef();

	const style = {
		left: 100 * index + '%',
		transform: `translateX(-${ 100 * currentStep }%)`,
	};

	const classes = [
		'wizard__step',
		isActive ? 'wizard__step--active' : '',
		valid ? 'wizard__step--valid' : 'wizard__step--invalid',
		currentStep > index ? 'wizard__step--done' : 'wizard__step--pending',
	]
		.filter( Boolean )
		.join( ' ' );

	const handleFocusTrapStart = ( event ) => {
		if ( ! isActive ) return;
		const focusables = ref.current?.querySelectorAll(
			'input, select, textarea, button, a[href], [tabindex]:not([tabindex="-1"])'
		);
		if ( focusables?.length > 0 ) {
			event.preventDefault();
			focusables[ focusables.length - 1 ].focus();
		}
	};

	const handleFocusTrapEnd = ( event ) => {
		console.log( 'handleFocusTrapEnd', isActive );
		if ( ! isActive ) return;
		const focusables = ref.current?.querySelectorAll(
			'input, select, textarea, button, a[href], [tabindex]:not([tabindex="-1"])'
		);
		if ( focusables?.length > 0 ) {
			event.preventDefault();
			focusables[ 0 ].focus();
		}
	};

	return (
		<div
			tabIndex={ isActive ? undefined : -1 }
			style={ style }
			className={ classes }
			role="dialog"
			aria-modal="true"
			ref={ ref }
		>
			<div tabIndex="0" style={ { width: 0, height: 0, overflow: 'hidden' } } onFocus={ handleFocusTrapStart } />

			{ children }

			<div tabIndex="0" style={ { overflow: 'hidden' } } onFocus={ handleFocusTrapEnd } />
		</div>
	);
};

export default WizardStep;
