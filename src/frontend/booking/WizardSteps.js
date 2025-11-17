import { Children, cloneElement } from '@wordpress/element';

const WizardSteps = (props) => {
	const { children, onNext, onPrev, state, dispatch } = props;

	const count = Children.count(children);

	const currentStep = state.wizard.step;

	const currentChild = Children.toArray(children)[currentStep];

	return (
		<div className="wizard">
			<div className="wizard__steps">
				{Children.map(children, (child, index) => {
					return cloneElement(child, { index, currentStep });
				})}
			</div>
		</div>
	);
};

export default WizardSteps;
