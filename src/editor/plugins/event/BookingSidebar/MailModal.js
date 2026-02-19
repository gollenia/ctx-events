import { __ } from '@wordpress/i18n';
import { Modal, Button } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';
	
import { useState } from '@wordpress/element';


const MailModal = ( { isOpen, onClose, eventTitle } ) => {
	const [ email, setEmail ] = useState( '' );

	const handleSaveEmail = () => {
		// Implement email sending logic here
		console.log( `Saving email to database` );
		onClose();
	};

	const tokenCompleter = {
	name: 'email-tokens',
	triggerPrefix: '{{',
	options: [
		{ key: 'bestellnr', label: 'Bestellnummer' },
		{ key: 'event_title', label: 'Eventtitel' },
		{ key: 'vorname', label: 'Vorname' },
	],

	getOptionLabel: ( option ) => option.label,

	getOptionKeywords: ( option ) => [
		option.key,
		option.label,
	],

	getOptionCompletion: ( option ) => ( {
		action: 'insert-at-caret',
		value: `{{${ option.key }}}`,
	} ),
};

	return (
		<Modal
			title={ __( 'Send Email', 'text-domain' ) }
			isOpen={ isOpen }
			onRequestClose={ onClose }
			shouldCloseOnOverlayClick={ true }
			shouldCloseOnEsc={ true }
		>
			<div>
				<p>{ __( 'Enter the email address to send information about the event:', 'text-domain' ) }</p>
				<RichText

					tagName="div"
					value={ email }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
					onChange={ setEmail }
					disableLineBreaks={ false }
					placeholder={ __( 'Enter email', 'text-domain' ) }
					multiline="br"
					autocompleters={[tokenCompleter]}
				/>
				<Button
					isPrimary
					onClick={ handleSaveEmail }	
				>
					{ __( 'Save Email', 'text-domain' ) }
				</Button>
			</div>
		</Modal>
	);
};

export default MailModal;