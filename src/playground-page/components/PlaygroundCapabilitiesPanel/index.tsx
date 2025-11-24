/**
 * External dependencies
 */
import type { AiCapabilityOption } from '@ai-services/playground-page/types';
import type { AiCapability } from '@ai-services/ai/types';
import { MultiCheckboxControl } from 'wp-admin-components';
import { store as interfaceStore, useInterfaceScope } from 'wp-interface';

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

type PlaygroundCapabilitiesPanelSelectProps = {
	availableFoundationalCapabilities: AiCapabilityOption[];
	availableAdditionalCapabilities: AiCapabilityOption[];
	foundationalCapability: AiCapability | undefined;
	additionalCapabilities: AiCapability[];
	isPanelOpened: boolean;
};

/**
 * Renders the playground sidebar panel for AI capabilities.
 *
 * @since 0.4.0
 * @returns The component to be rendered.
 */
export default function PlaygroundCapabilitiesPanel() {
	const scope = useInterfaceScope();

	const {
		availableFoundationalCapabilities,
		availableAdditionalCapabilities,
		foundationalCapability,
		additionalCapabilities,
		isPanelOpened,
	}: PlaygroundCapabilitiesPanelSelectProps = useSelect(
		( select ) => {
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
				foundationalCapability:
					getFoundationalCapability() as AiCapability,
				additionalCapabilities:
					getAdditionalCapabilities() as AiCapability[],
				isPanelOpened: isPanelActive(
					scope,
					'playground-capabilities',
					true
				),
			};
		},
		[ scope ]
	);

	const { setFoundationalCapability, toggleAdditionalCapability } =
		useDispatch( playgroundStore );
	const { togglePanel } = useDispatch( interfaceStore );

	// Get option objects for available additional capabilities to render in the checkbox list.
	const additionalCapabilityOptions: Array< {
		value: AiCapability;
		label: string;
	} > = useMemo( () => {
		return availableAdditionalCapabilities.map(
			( cap: AiCapabilityOption ) => {
				return {
					value: cap.identifier,
					label: cap.label,
				};
			}
		);
	}, [ availableAdditionalCapabilities ] );

	return (
		<PanelBody
			title={ __( 'Capabilities', 'ai-services' ) }
			opened={ isPanelOpened }
			onToggle={ () => togglePanel( scope, 'playground-capabilities' ) }
			className="ai-services-playground-capabilities-panel"
		>
			<Flex direction="column" gap="4">
				<SelectControl
					className="ai-services-playground-foundational-capability"
					label={ __( 'Foundational capability', 'ai-services' ) }
					value={ foundationalCapability }
					options={ availableFoundationalCapabilities.map(
						( { identifier, label }: AiCapabilityOption ) => ( {
							value: identifier as AiCapability,
							label,
						} )
					) }
					onChange={ ( value: AiCapability ) =>
						setFoundationalCapability( value )
					}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<MultiCheckboxControl
					label={ __( 'Additional capabilities', 'ai-services' ) }
					className="ai-services-playground-additional-capabilities"
					value={ additionalCapabilities }
					options={ additionalCapabilityOptions }
					onToggle={ ( value: string ) =>
						toggleAdditionalCapability( value as AiCapability )
					}
					__nextHasNoMarginBottom
				/>
			</Flex>
		</PanelBody>
	);
}
