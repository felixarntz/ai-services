/**
 * External dependencies
 */
import { MultiCheckboxControl, HelpText } from '@ai-services/components';
import { store as interfaceStore } from '@ai-services/interface';
import { enums, store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { Flex, FlexItem, PanelBody, Button } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';
import './style.scss';

const EMPTY_ARRAY = [];
const MIN_FUNCTION_DECLARATIONS_COUNT_FOR_FILTER = 8;

/**
 * Renders the playground sidebar panel for function declarations relevant for AI function calling.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function PlaygroundFunctionDeclarationsPanel() {
	const {
		capabilities,
		availableFunctionDeclarations,
		selectedFunctionDeclarations,
		isPanelOpened,
	} = useSelect( ( select ) => {
		const { getServices } = select( aiStore );
		const {
			getService,
			getModel,
			getFunctionDeclarations,
			getSelectedFunctionDeclarations,
		} = select( playgroundStore );
		const { isPanelActive } = select( interfaceStore );

		const currentService = getService();
		const currentModel = getModel();

		let currentCapabilities = EMPTY_ARRAY;
		if ( currentService && currentModel ) {
			const services = getServices();
			if (
				services &&
				services[ currentService ] &&
				services[ currentService ].available_models[ currentModel ]
			) {
				currentCapabilities =
					services[ currentService ].available_models[ currentModel ]
						.capabilities;
			}
		}

		return {
			capabilities: currentCapabilities,
			availableFunctionDeclarations: getFunctionDeclarations(),
			selectedFunctionDeclarations: getSelectedFunctionDeclarations(),
			isPanelOpened: isPanelActive( 'playground-function-declarations' ),
		};
	} );

	const { toggleSelectedFunctionDeclaration } =
		useDispatch( playgroundStore );
	const { togglePanel, openModal } = useDispatch( interfaceStore );

	// Get option objects for available function declarations to render in the checkbox list.
	const functionDeclarationOptions = useMemo( () => {
		return availableFunctionDeclarations.map( ( functionDeclaration ) => {
			return {
				value: functionDeclaration.name,
				label: `${ functionDeclaration.name }()`,
			};
		} );
	}, [ availableFunctionDeclarations ] );

	if ( ! capabilities.includes( enums.AiCapability.FUNCTION_CALLING ) ) {
		return null;
	}

	const showFilter =
		availableFunctionDeclarations.length >=
		MIN_FUNCTION_DECLARATIONS_COUNT_FOR_FILTER;

	return (
		<PanelBody
			title={ __( 'Function declarations', 'ai-services' ) }
			opened={ isPanelOpened }
			onToggle={ () => togglePanel( 'playground-function-declarations' ) }
			className="ai-services-playground-function-declarations-panel"
		>
			<Flex direction="column" gap="4">
				<HelpText>
					{ __(
						'Select which functions to make available to the AI model.',
						'ai-services'
					) }
				</HelpText>
				<MultiCheckboxControl
					label={ __(
						'Selected function declarations',
						'ai-services'
					) }
					hideLabelFromVision
					className="ai-services-playground-function-declarations-panel__function-declarations"
					value={ selectedFunctionDeclarations }
					options={ functionDeclarationOptions }
					onToggle={ toggleSelectedFunctionDeclaration }
					showFilter={ showFilter }
					__nextHasNoMarginBottom
				/>
				<FlexItem>
					<Button
						onClick={ () => {
							openModal( 'function-declarations' );
						} }
						variant="link"
					>
						{ availableFunctionDeclarations.length > 0
							? __(
									'Manage function declarations',
									'ai-services'
							  )
							: __( 'Add function declarations', 'ai-services' ) }
					</Button>
				</FlexItem>
			</Flex>
		</PanelBody>
	);
}
