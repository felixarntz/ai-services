/**
 * External dependencies
 */
import { SensitiveTextControl } from '@ai-services/components';
import { store as pluginSettingsStore } from '@ai-services/settings-store';

/**
 * WordPress dependencies
 */
import {
	Card,
	CardHeader,
	CardBody,
	ExternalLink,
	ToggleControl,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { createInterpolateElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

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
						<SensitiveTextControl
							key={ service.slug }
							label={ service.name }
							HelpContent={ () => (
								<>
									{ service.has_forced_api_key
										? sprintf(
												/* translators: %s: service name */
												__(
													'The API key for %s cannot be modified as its value is enforced via filter.',
													'ai-services'
												),
												service.name
										  )
										: sprintf(
												/* translators: %s: service name */
												__(
													'Enter the API key for %s.',
													'ai-services'
												),
												service.name
										  ) }{ ' ' }
									{ !! service.credentials_url && (
										<ExternalLink
											href={ service.credentials_url }
										>
											{ createInterpolateElement(
												!! service.apiKey
													? sprintf(
															/* translators: %s: service name */
															__(
																'Manage<span> %s</span> API keys',
																'ai-services'
															),
															service.name
													  )
													: sprintf(
															/* translators: %s: service name */
															__(
																'Get<span> %s</span> API key',
																'ai-services'
															),
															service.name
													  ),
												{
													span: (
														<span className="screen-reader-text" />
													),
												}
											) }
										</ExternalLink>
									) }
								</>
							) }
							readOnly={ service.has_forced_api_key }
							disabled={ service.apiKey === undefined }
							value={ service.apiKey || '' }
							onChange={ ( value ) =>
								setApiKey( service.slug, value )
							}
							buttonShowLabel={ sprintf(
								/* translators: %s: service name */
								__( 'Show API key for %s.', 'ai-services' ),
								service.name
							) }
							buttonHideLabel={ sprintf(
								/* translators: %s: service name */
								__( 'Hide API key for %s.', 'ai-services' ),
								service.name
							) }
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
					/>
				</CardBody>
			</Card>
		</div>
	);
}
