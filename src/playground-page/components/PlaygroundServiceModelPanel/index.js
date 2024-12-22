/**
 * External dependencies
 */
import { store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { PanelBody, Notice, SelectControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	createInterpolateElement,
	useMemo,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';
import './style.scss';

const MODEL_SELECT_PLACEHOLDER_OPTIONS = [
	{
		value: '',
		label: __( 'Select service to see models', 'ai-services' ),
	},
];

/**
 * Renders the playground sidebar panel for AI service and model selection.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function PlaygroundServiceModelPanel() {
	const [ panelOpened, setPanelOpened ] = useState( true );

	const {
		availableServices,
		availableModels,
		service,
		model,
		servicesSettingsUrl,
		currentUserCanManageServices,
	} = useSelect( ( select ) => {
		const {
			getAvailableServices,
			getAvailableModels,
			getService,
			getModel,
		} = select( playgroundStore );
		const { getPluginSettingsUrl, currentUserCan } = select( aiStore );

		return {
			availableServices: getAvailableServices(),
			availableModels: getAvailableModels(),
			service: getService(),
			model: getModel(),
			servicesSettingsUrl: getPluginSettingsUrl(),
			currentUserCanManageServices: currentUserCan(
				'ais_manage_services'
			),
		};
	} );

	const { setService, setModel } = useDispatch( playgroundStore );

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

	return (
		<PanelBody
			title={ __( 'Model selection', 'ai-services' ) }
			opened={ panelOpened }
			onToggle={ () => setPanelOpened( ! panelOpened ) }
			className="ai-services-playground-service-model-panel"
		>
			{ availableServices.length ? (
				<>
					<SelectControl
						className="ai-services-playground-service"
						label={ __( 'Service', 'ai-services' ) }
						value={ service }
						options={ serviceSelectOptions }
						onChange={ ( value ) => setService( value ) }
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
						onChange={ ( value ) => setModel( value ) }
						disabled={ modelSelectOptions.length <= 1 }
						__nextHasNoMarginBottom
					/>
				</>
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