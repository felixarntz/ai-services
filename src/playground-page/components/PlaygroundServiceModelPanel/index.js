/**
 * External dependencies
 */
import { store as aiStore } from '@ai-services/ai';
import { store as interfaceStore } from '@ai-services/interface';

/**
 * WordPress dependencies
 */
import { Flex, PanelBody, Notice, SelectControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	createInterpolateElement,
	useEffect,
	useMemo,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { speak } from '@wordpress/a11y';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';

const MODEL_SELECT_PLACEHOLDER_OPTIONS = [
	{
		value: '',
		label: __( 'Select service to see models', 'ai-services' ),
	},
];

/**
 * Renders the playground sidebar panel for AI service and model selection.
 *
 * @since 0.4.0
 *
 * @return {Component} The component to be rendered.
 */
export default function PlaygroundServiceModelPanel() {
	const {
		availableServices,
		availableModels,
		service,
		model,
		servicesSettingsUrl,
		currentUserCanManageServices,
		isPanelOpened,
	} = useSelect( ( select ) => {
		const {
			getAvailableServices,
			getAvailableModels,
			getService,
			getModel,
		} = select( playgroundStore );
		const { getPluginSettingsUrl, currentUserCan } = select( aiStore );
		const { isPanelActive } = select( interfaceStore );

		return {
			availableServices: getAvailableServices(),
			availableModels: getAvailableModels(),
			service: getService(),
			model: getModel(),
			servicesSettingsUrl: getPluginSettingsUrl(),
			currentUserCanManageServices: currentUserCan(
				'ais_manage_services'
			),
			isPanelOpened: isPanelActive( 'playground-service-model', true ),
		};
	} );

	const { setService, setModel } = useDispatch( playgroundStore );
	const { togglePanel } = useDispatch( interfaceStore );

	const serviceSelectOptions = useMemo( () => {
		return [
			{
				value: '',
				label: __( 'Select service…', 'ai-services' ),
			},
			...availableServices.map( ( { identifier, label } ) => ( {
				value: identifier,
				label,
			} ) ),
		];
	}, [ availableServices ] );

	const modelSelectOptions = useMemo( () => {
		return [
			{
				value: '',
				label: __( 'Select model…', 'ai-services' ),
			},
			...availableModels.map( ( { identifier, label } ) => ( {
				value: identifier,
				label,
			} ) ),
		];
	}, [ availableModels ] );

	const [ changedService, setChangedService ] = useState( false );
	const onChangeService = ( value ) => {
		setService( value );
		setChangedService( true );
	};

	// Announce to screen readers when model selection was cleared after a service change.
	useEffect( () => {
		if ( ! changedService ) {
			return;
		}

		setChangedService( false );
		if ( ! model ) {
			speak(
				__(
					'Please continue navigating to select a model.',
					'ai-services'
				),
				'polite'
			);
		}
	}, [ changedService, model ] );

	return (
		<PanelBody
			title={ __( 'Model selection', 'ai-services' ) }
			opened={ isPanelOpened }
			onToggle={ () => togglePanel( 'playground-service-model' ) }
			className="ai-services-playground-service-model-panel"
		>
			{ availableServices.length ? (
				<Flex direction="column" gap="4">
					<SelectControl
						className="ai-services-playground-service"
						label={ __( 'Service', 'ai-services' ) }
						value={ service }
						options={ serviceSelectOptions }
						onChange={ onChangeService }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						className="ai-services-playground-model"
						label={ __( 'Model', 'ai-services' ) }
						value={ model }
						options={
							modelSelectOptions.length > 1
								? modelSelectOptions
								: MODEL_SELECT_PLACEHOLDER_OPTIONS
						}
						onChange={ setModel }
						disabled={ modelSelectOptions.length <= 1 }
						__nextHasNoMarginBottom
					/>
				</Flex>
			) : (
				<Notice status="warning" isDismissible={ false }>
					{ __(
						'No services available for the configured capabilities.',
						'ai-services'
					) }
					{ currentUserCanManageServices &&
						createInterpolateElement(
							' ' +
								__(
									'Please configure <a>AI services</a>.',
									'ai-services'
								),
							{
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								a: <a href={ servicesSettingsUrl } />,
							}
						) }
				</Notice>
			) }
		</PanelBody>
	);
}
