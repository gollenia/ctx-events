/**
 * Wordpress dependencies
 */
import {
	Inserter,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { select, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export default function Edit({ ...props }) {
	const { clientId } = props;
	const blockProps = useBlockProps();

	const postType = props.context.postType;
	if (!['ctx-booking-form', 'ctx-attendee-form'].includes(postType)) return null;

	document
		.getElementsByClassName('edit-post-fullscreen-mode-close')[0]
		?.setAttribute('href', 'edit.php?post_type=event&page=events-forms');

	const allowedBlocks = [
		'ctx-events/form-text',
		'ctx-events/form-email',
		'ctx-events/form-textarea',
		'ctx-events/form-select',
		'ctx-events/form-country',
		'ctx-events/form-phone',
		'ctx-events/form-radio',
		'ctx-events/form-checkbox',
		'ctx-events/form-date',
		'ctx-events/form-html',
	];

	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		allowedBlocks,
		templateLock: false,
	});

	function SectionAppender({ rootClientId }) {
		return (
			<Inserter
				allowedBlocks={allowedBlocks}
				rootClientId={rootClientId}
				renderToggle={({ onToggle, disabled }) => (
					<a className="components-button is-primary" onClick={onToggle}>
						{__('Add Field', 'ctx-events')}
					</a>
				)}
				isAppender
			/>
		);
	}

	return (
		<form autocomplete="off" className="ctx:event-form">
			<div {...innerBlocksProps} className="ctx:event-form__container"></div>
			<div className="ctx:event-form__appender">
				<SectionAppender rootClientId={clientId} />
			</div>
		</form>
	);
}
