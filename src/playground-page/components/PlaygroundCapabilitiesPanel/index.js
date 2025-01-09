/**
 * External dependencies
 */
import { store as interfaceStore } from '@ai-services/interface';

/**
 * WordPress dependencies
 */
import {
	PanelBody,
	CheckboxControl,
	SelectControl,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';
import './style.scss';

/**
 * Renders the playground sidebar panel for AI capabilities.
 *
 * @since 0.4.0
 *
 * @return {Component} The component to be rendered.
 */
export default function PlaygroundCapabilitiesPanel() {
	const {
		availableFoundationalCapabilities,
		availableAdditionalCapabilities,
		foundationalCapability,
		additionalCapabilities,
		isPanelOpened,
	} = useSelect( ( select ) => {
		const {
			getAvailableFoundationalCapabilities,
			getAvailableAdditionalCapabilities,
			getFoundationalCapability,
			getAdditionalCapabilities,
		} = select( playgroundStore );
		const { isPanelActive } = select( interfaceStore );

		return {
			availableFoundationalCapabilities:
				getAvailableFoundationalCapabilities(),
			availableAdditionalCapabilities:
				getAvailableAdditionalCapabilities(),
			foundationalCapability: getFoundationalCapability(),
			additionalCapabilities: getAdditionalCapabilities(),
			isPanelOpened: isPanelActive( 'playground-capabilities', true ),
		};
	} );

	const { setFoundationalCapability, toggleAdditionalCapability } =
		useDispatch( playgroundStore );
	const { togglePanel } = useDispatch( interfaceStore );

	return (
		<PanelBody
			title={ __( 'Capabilities', 'ai-services' ) }
			opened={ isPanelOpened }
			onToggle={ () => togglePanel( 'playground-capabilities' ) }
			className="ai-services-playground-capabilities-panel"
		>
			<SelectControl
				className="ai-services-playground-foundational-capability"
				label={ __( 'Foundational Capability', 'ai-services' ) }
				value={ foundationalCapability }
				options={ availableFoundationalCapabilities.map(
					( { identifier, label } ) => ( {
						value: identifier,
						label,
					} )
				) }
				onChange={ ( value ) => setFoundationalCapability( value ) }
				disabled={ availableFoundationalCapabilities.length < 2 }
				__nextHasNoMarginBottom
			/>
			<fieldset className="ai-services-playground-additional-capabilities">
				<legend>
					{ __( 'Additional Capabilities', 'ai-services' ) }
				</legend>
				{ availableAdditionalCapabilities.map(
					( { identifier, label } ) => (
						<CheckboxControl
							key={ identifier }
							label={ label }
							checked={ additionalCapabilities.includes(
								identifier
							) }
							onChange={ () =>
								toggleAdditionalCapability( identifier )
							}
							__nextHasNoMarginBottom
						/>
					)
				) }
			</fieldset>
		</PanelBody>
	);
}
