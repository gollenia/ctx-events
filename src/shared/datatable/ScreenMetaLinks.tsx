interface ScreenMetaProps {
	setScreenMeta: (context: string) => void;
}

const ScreenMetaLinks = ({ setScreenMeta }: ScreenMetaProps) => {
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
					aria-expanded="false"
					onClick={() => setScreenMeta('options')}
				>
					Ansicht anpassen
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
					Hilfe
				</button>
			</div>
		</div>
	);
};

export default ScreenMetaLinks;
