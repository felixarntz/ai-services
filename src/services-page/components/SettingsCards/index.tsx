/**
 * External dependencies
 */
import memoize from 'memize';
import { ApiKeyControl } from '@ai-services/components';
import { store as pluginSettingsStore } from '@ai-services/settings';
import { ServiceResource } from '@ai-services/ai/types';

/**
 * WordPress dependencies
 */
import {
	Card,
	CardHeader,
	CardBody,
	ToggleControl,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

const EMPTY_SERVICES_ARRAY: ServiceResource[] = [];

const unmemoizedMapValuesToArray: < T >( map: Record< string, T > ) => T[] = (
	map
) => Object.values( map );
const mapValuesToArray = memoize(
	unmemoizedMapValuesToArray
) as typeof unmemoizedMapValuesToArray;

type ServiceApiKeyControlProps = {
	service: ServiceResource;
	className?: string;
};

/**
 * Renders the API key control for a specific service.
 *
 * This is needed to avoid merging the API key into the service object.
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
function ServiceApiKeyControl( props: ServiceApiKeyControlProps ) {
	const { service, className } = props;

	const apiKey = useSelect(
		( select ) => select( pluginSettingsStore ).getApiKey( service.slug ),
		[ service.slug ]
	);

	const { setApiKey } = useDispatch( pluginSettingsStore );

	const onChangeApiKey = useCallback(
		( newApiKey: string, serviceSlug: string ) =>
			setApiKey( serviceSlug, newApiKey ),
		[ setApiKey ]
	);

	return (
		<ApiKeyControl
			service={ service }
			apiKey={ apiKey }
			onChangeApiKey={ onChangeApiKey }
			className={ className }
		/>
	);
}

/**
 * Renders the cards for all the settings controls.
 *
 * @since n.e.x.t
 *
 * @returns The component to be rendered.
 */
export default function SettingsCards() {
	const { isLoadingSettings, services, deleteData } = useSelect(
		( select ) => {
			const { getServices, getSettings, isResolving, getDeleteData } =
				select( pluginSettingsStore );

			const servicesMap = getServices();

			return {
				isLoadingSettings:
					getSettings() === undefined || isResolving( 'getSettings' ),
				services:
					servicesMap !== undefined
						? mapValuesToArray( servicesMap )
						: EMPTY_SERVICES_ARRAY,
				deleteData: getDeleteData(),
			};
		},
		[]
	);

	const { setDeleteData } = useDispatch( pluginSettingsStore );

	return (
		<div className="ais-settings-cards">
			<Card>
				<CardHeader>
					<h2 className="ais-settings-cards__heading">
						{ __( 'API Keys', 'ai-services' ) }
					</h2>
				</CardHeader>
				<CardBody>
					{ services.map( ( service ) => (
						<ServiceApiKeyControl
							key={ service.slug }
							service={ service }
							className="ais-settings-cards__api-key-control"
						/>
					) ) }
				</CardBody>
			</Card>
			<Card>
				<CardHeader>
					<h2 className="ais-settings-cards__heading">
						{ __( 'Advanced', 'ai-services' ) }
					</h2>
				</CardHeader>
				<CardBody>
					<ToggleControl
						label={ __(
							'Delete plugin data upon uninstallation',
							'ai-services'
						) }
						help={ __(
							'By default no data will be deleted, should you decide to uninstall the AI Services plugin. If you are certain that you want the data to be deleted, please enable this toggle.',
							'ai-services'
						) }
						disabled={ isLoadingSettings }
						checked={ deleteData || false }
						onChange={ setDeleteData }
						__nextHasNoMarginBottom
					/>
				</CardBody>
			</Card>
		</div>
	);
}
