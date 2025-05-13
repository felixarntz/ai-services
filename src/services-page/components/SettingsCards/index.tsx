/**
 * External dependencies
 */
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
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Renders the cards for all the settings controls.
 *
 * @since n.e.x.t
 *
 * @returns The component to be rendered.
 */
export default function SettingsCards() {
	const { isLoading, deleteData } = useSelect( ( select ) => {
		const { getSettings, isResolving, getDeleteData } =
			select( pluginSettingsStore );

		return {
			isLoading:
				getSettings() === undefined || isResolving( 'getSettings' ),
			deleteData: getDeleteData(),
		};
	}, [] );

	const { setDeleteData } = useDispatch( pluginSettingsStore );

	return (
		<div className="wpsp-settings-cards">
			<Card>
				<CardHeader>
					<h2 className="wpsp-settings-cards__heading">
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
						disabled={ isLoading }
						checked={ deleteData || false }
						onChange={ setDeleteData }
						__nextHasNoMarginBottom
					/>
				</CardBody>
			</Card>
		</div>
	);
}
