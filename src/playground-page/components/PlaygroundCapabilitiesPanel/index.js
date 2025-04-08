/**
 * External dependencies
 */
import { MultiCheckboxControl } from '@ai-services/components';
import { store as interfaceStore } from '@ai-services/interface';

/**
 * WordPress dependencies
 */
import { Flex, PanelBody, SelectControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';

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

	// Get option objects for available additional capabilities to render in the checkbox list.
	const additionalCapabilityOptions = useMemo( () => {
		return availableAdditionalCapabilities.map( ( cap ) => {
			return {
				value: cap.identifier,
				label: cap.label,
			};
		} );
	}, [ availableAdditionalCapabilities ] );

	return (
		<PanelBody
			title={ __( 'Capabilities', 'ai-services' ) }
			opened={ isPanelOpened }
			onToggle={ () => togglePanel( 'playground-capabilities' ) }
			className="ai-services-playground-capabilities-panel"
		>
			<Flex direction="column" gap="4">
				<SelectControl
					className="ai-services-playground-foundational-capability"
					label={ __( 'Foundational capability', 'ai-services' ) }
					value={ foundationalCapability }
					options={ availableFoundationalCapabilities.map(
						( { identifier, label } ) => ( {
							value: identifier,
							label,
						} )
					) }
					onChange={ ( value ) => setFoundationalCapability( value ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<MultiCheckboxControl
					label={ __( 'Additional capabilities', 'ai-services' ) }
					className="ai-services-playground-additional-capabilities"
					value={ additionalCapabilities }
					options={ additionalCapabilityOptions }
					onToggle={ toggleAdditionalCapability }
					__nextHasNoMarginBottom
				/>
			</Flex>
		</PanelBody>
	);
}
