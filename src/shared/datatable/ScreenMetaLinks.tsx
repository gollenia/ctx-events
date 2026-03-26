import { __ } from '@wordpress/i18n';
import { useDataTable } from './DataTableContext';

interface ScreenMetaProps {
	setScreenMeta: (context: string) => void;
	screenMeta: string;
}

const ScreenMetaLinks = ({ setScreenMeta, screenMeta }: ScreenMetaProps) => {
	return (
		<div id="screen-meta-links">
			<div
				id="screen-options-link-wrap"
				className="hide-if-no-js screen-meta-toggle"
			>
				<button
					type="button"
					id="show-settings-link"
					className="button show-settings"
					aria-controls="screen-options-wrap"
					aria-expanded={screenMeta === 'options'}
					onClick={() => {
						if (screenMeta !== 'options') {
							setScreenMeta('options');
						} else {
							setScreenMeta('');
						}
					}}
				>
					{__('Screen Options', 'ctx-events')}
				</button>
			</div>
			<div
				id="contextual-help-link-wrap"
				className="hide-if-no-js screen-meta-toggle"
				style={{}}
			>
				<button
					type="button"
					id="contextual-help-link"
					className="button show-settings"
					aria-controls="contextual-help-wrap"
					aria-expanded="false"
					onClick={() => setScreenMeta('help')}
				>
					{__('Help', 'ctx-events')}
				</button>
			</div>
		</div>
	);
};

const DataTableScreenMetaLinks = () => {
	const { setScreenMetaContext, screenMetaContext } = useDataTable();
	return (
		<ScreenMetaLinks
			setScreenMeta={setScreenMetaContext}
			screenMeta={screenMetaContext}
		/>
	);
};

export default ScreenMetaLinks;
export { DataTableScreenMetaLinks };
