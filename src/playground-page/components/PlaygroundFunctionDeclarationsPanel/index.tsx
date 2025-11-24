/**
 * External dependencies
 */
import { enums, store as aiStore } from '@ai-services/ai';
import type {
	AiCapability,
	FunctionDeclaration,
	ServiceResource,
} from '@ai-services/ai/types';
import { MultiCheckboxControl, HelpText } from 'wp-admin-components';
import { store as interfaceStore, useInterfaceScope } from 'wp-interface';

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

const EMPTY_CAPABILITY_ARRAY: AiCapability[] = [];
const MIN_FUNCTION_DECLARATIONS_COUNT_FOR_FILTER = 8;

type FunctionDeclarationOption = {
	value: string;
	label: string;
};

/**
 * Renders the playground sidebar panel for function declarations relevant for AI function calling.
 *
 * @since 0.5.0
 *
 * @returns The component to be rendered.
 */
export default function PlaygroundFunctionDeclarationsPanel() {
	const scope = useInterfaceScope();

	const {
		capabilities,
		availableFunctionDeclarations,
		selectedFunctionDeclarations,
		isPanelOpened,
	} = useSelect(
		( select ) => {
			const { getServices } = select( aiStore );
			const {
				getService,
				getModel,
				getFunctionDeclarations,
				getSelectedFunctionDeclarations,
			} = select( playgroundStore );
			const { isPanelActive } = select( interfaceStore );

			const currentService: string | false = getService();
			const currentModel: string | false = getModel();

			let currentCapabilities: AiCapability[] = EMPTY_CAPABILITY_ARRAY;
			if ( currentService && currentModel ) {
				const services: Record< string, ServiceResource > | undefined =
					getServices();
				if (
					services &&
					services[ currentService ] &&
					services[ currentService ].available_models[ currentModel ]
				) {
					currentCapabilities =
						services[ currentService ].available_models[
							currentModel
						].capabilities;
				}
			}

			return {
				capabilities: currentCapabilities,
				availableFunctionDeclarations:
					getFunctionDeclarations() as FunctionDeclaration[],
				selectedFunctionDeclarations:
					getSelectedFunctionDeclarations() as string[],
				isPanelOpened: isPanelActive(
					scope,
					'playground-function-declarations'
				) as boolean,
			};
		},
		[ scope ]
	);

	const { toggleSelectedFunctionDeclaration } =
		useDispatch( playgroundStore );
	const { togglePanel, openModal } = useDispatch( interfaceStore );

	// Get option objects for available function declarations to render in the checkbox list.
	const functionDeclarationOptions: FunctionDeclarationOption[] =
		useMemo( () => {
			return availableFunctionDeclarations.map(
				( functionDeclaration: FunctionDeclaration ) => {
					return {
						value: functionDeclaration.name,
						label: `${ functionDeclaration.name }()`,
					};
				}
			);
		}, [ availableFunctionDeclarations ] );

	if ( ! capabilities.includes( enums.AiCapability.FUNCTION_CALLING ) ) {
		return null;
	}

	const showFilter: boolean =
		availableFunctionDeclarations.length >=
		MIN_FUNCTION_DECLARATIONS_COUNT_FOR_FILTER;

	return (
		<PanelBody
			title={ __( 'Function declarations', 'ai-services' ) }
			opened={ isPanelOpened }
			onToggle={ () =>
				togglePanel( scope, 'playground-function-declarations' )
			}
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
					searchLabel={ __(
						'Search function declarations',
						'ai-services'
					) }
					messageSearchResultFound={
						/* translators: %d: number of results */
						__( '%d result found.', 'ai-services' )
					}
					messageSearchResultsFound={
						/* translators: %d: number of results */
						__( '%d results found.', 'ai-services' )
					}
					__nextHasNoMarginBottom
				/>
				<FlexItem>
					<Button
						onClick={ () => {
							openModal( scope, 'function-declarations' );
						} }
						variant="link"
						__next40pxDefaultSize
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
