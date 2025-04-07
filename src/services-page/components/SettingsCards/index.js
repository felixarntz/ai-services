/**
 * External dependencies
 */
import { ApiKeyControl } from '@ai-services/components';
import { store as pluginSettingsStore } from '@ai-services/settings';

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

const EMPTY_ARRAY = [];

/**
 * Renders the cards for all the settings controls.
 *
 * @since 0.1.0
 *
 * @return {Component} The component to be rendered.
 */
export default function SettingsCards() {
	const { isLoadingSettings, services, deleteData } = useSelect(
		( select ) => {
			const {
				getServices,
				getSettings,
				isResolving,
				getApiKey,
				getDeleteData,
			} = select( pluginSettingsStore );

			return {
				isLoadingSettings:
					getSettings() === undefined || isResolving( 'getSettings' ),
				services:
					getServices() !== undefined
						? Object.values( getServices() ).map( ( service ) => {
								return {
									...service,
									apiKey: getApiKey( service.slug ),
								};
						  } )
						: EMPTY_ARRAY,
				deleteData: getDeleteData(),
			};
		}
	);

	const { setApiKey, setDeleteData } = useDispatch( pluginSettingsStore );

	const onChangeApiKey = useCallback(
		( newApiKey, serviceSlug ) => setApiKey( serviceSlug, newApiKey ),
		[ setApiKey ]
	);

	return (
		<div className="ais-settings-cards">
			<Card>
				<CardHeader>
					<h2 className="ais-settings-cards__heading">
						{ __( 'API Keys', 'ai-services' ) }
					</h2>
				</CardHeader>
				<CardBody>
					{ services.map( ( { apiKey, ...service } ) => (
						<ApiKeyControl
							key={ service.slug }
							service={ service }
							apiKey={ apiKey }
							onChangeApiKey={ onChangeApiKey }
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
