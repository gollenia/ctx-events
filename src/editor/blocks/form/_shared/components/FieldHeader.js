import { RichText } from '@wordpress/block-editor';
import { Icon, Tooltip } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { isValidSlug, sanitizeSlug, isSlugLocked, isValidLabel, SlugStatus } from '../utils/validation'; 
import lock from './lockIcon'; // Pfad anpassen
import useDependencyLock from '../hooks/useDependencyLock';

const FieldHeader = ({ attributes, setAttributes, clientId, icon = null, helpText = null }) => {
    const { label, name, required } = attributes;
    const description = helpText || __('Label for the field', 'events');

    const isSystemLocked = isSlugLocked(name);
	const isReferenced = useDependencyLock(clientId, name);
	const isLocked = isReferenced || isSystemLocked;

    const isSlugValid = isValidSlug(name);
    const isLabelValid = isValidLabel(label);

	let lockReason = '';
    if (isSystemLocked) {
        lockReason += __('System field: Cannot be changed.', 'events');
    } 
    if (isReferenced) {
        lockReason += __('Locked: Used by another field as visibility condition.', 'events');
    }

    const onChangeName = (value) => {
        setAttributes({ name: sanitizeSlug(value) });
    };

    return (
        <div className="ctx:event-field__caption">
            {/* Linke Seite: Icon (optional) + Label */}
            <div className="ctx:event-field__info">
                {icon && <Icon icon={icon} className="ctx:event-field__icon" />}
                
                <div className="ctx:event-field__description">
                    <span>
                        <RichText
                            tagName="span"
                            className={`ctx:event-field__label ${!isLabelValid ? 'ctx:input-error' : ''}`}
                            value={label}
                            placeholder={__('Label', 'events')}
                            onChange={(value) => setAttributes({ label: value })}
                            allowedFormats={[]} 
                        />
                        <span>{required ? '*' : ''}</span>
                    </span>
                    
                    <span className="ctx:event-field__sublabel">
                        {description}
                    </span>
                </div>
            </div>

            <div className="ctx:event-field__slug">
                {!isLocked ? (
                    <RichText
                        tagName="p"
                        className="ctx:event-field__slug-input"
                        value={name}
                        placeholder={__('Slug', 'events')}
                        onChange={onChangeName}
                        allowedFormats={[]}
                    />
                ) : (
                    
						<Tooltip text={lockReason}>
                        <span className="ctx:event-field__slug--lock" style={{ cursor: 'help' }}>
                            <span className="ctx:event-field__slug-input">{name}</span> <Icon icon={lock} size={14} />
                        </span>
                    </Tooltip>
                )}
                
                { isSlugValid ? (
                    <span className="ctx:event-field__sublabel">
                		{__('Unique identifier', 'events')}
            		</span>
                ) : (
                    <span className="ctx:event-field__error-message">
                        {__('Please type in a unique identifier for the field', 'events')}
                    </span>
                )}
            </div>
        </div>
    );
};

export default FieldHeader;