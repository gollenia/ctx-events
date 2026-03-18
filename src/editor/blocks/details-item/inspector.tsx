import {
	__experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
	InspectorControls,
	__experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
} from '@wordpress/block-editor';
import { PanelBody, PanelRow, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type {
	ColorValue,
	DetailsItemAttributes,
	SetBlockAttributes,
} from '@events/details/types';

type InspectorProps = {
	attributes: DetailsItemAttributes;
	clientId: string;
	iconColor?: ColorValue;
	iconBackgroundColor?: ColorValue;
	setAttributes: SetBlockAttributes<DetailsItemAttributes>;
	setIconColor: (value?: string) => void;
	setIconBackgroundColor: (value?: string) => void;
};

const Inspector = (props: InspectorProps) => {
	const {
		attributes,
		setAttributes,
		iconColor,
		iconBackgroundColor,
		setIconColor,
		setIconBackgroundColor,
		clientId,
	} = props;

	const colorGradientSettings = useMultipleOriginColorsAndGradients();
	const { url, urlIcon, icon, customIconBackgroundColor, customIconColor } =
		attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Behaviour', 'ctx-events')} initialOpen={true}>
					<TextControl
						label={__('Link', 'ctx-events')}
						value={url}
						onChange={(value) => setAttributes({ url: value })}
					/>
					<TextControl
						label={__('Icon for Link', 'ctx-events')}
						value={urlIcon}
						onChange={(value) => setAttributes({ urlIcon: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Image', 'ctx-events')} initialOpen={true}>
					<PanelRow>
						<TextControl
							label={__('Icon', 'ctx-events')}
							value={icon}
							onChange={(value) => setAttributes({ icon: value })}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<InspectorControls group="color">
				<ColorGradientSettingsDropdown
					settings={[
						{
							label: __('Icon Background', 'ctx-events'),
							colorValue:
								iconBackgroundColor?.color || customIconBackgroundColor,
							onColorChange: (value) => {
								setIconBackgroundColor(value);
								setAttributes({
									customIconBackgroundColor: value ?? '',
								});
							},
						},
					]}
					panelId={clientId}
					hasColorsOrGradients={false}
					disableCustomColors={false}
					__experimentalIsRenderedInSidebar
					{...colorGradientSettings}
				/>
				<ColorGradientSettingsDropdown
					settings={[
						{
							label: __('Icon Color', 'ctx-events'),
							colorValue: iconColor?.color || customIconColor,
							onColorChange: (value) => {
								setIconColor(value);
								setAttributes({
									customIconColor: value ?? '',
								});
							},
						},
					]}
					panelId={clientId}
					hasColorsOrGradients={false}
					disableCustomColors={false}
					__experimentalIsRenderedInSidebar
					{...colorGradientSettings}
				/>
			</InspectorControls>
		</>
	);
};

export default Inspector;
