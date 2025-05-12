/**
 * External dependencies
 */
import { store as pluginSettingsStore } from '@wp-starter-plugin/settings-store';

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
						{ __( 'Advanced', 'wp-starter-plugin' ) }
					</h2>
				</CardHeader>
				<CardBody>
					<ToggleControl
						label={ __(
							'Delete plugin data upon uninstallation',
							'wp-starter-plugin'
						) }
						help={ __(
							'By default no data will be deleted, should you decide to uninstall the WP Starter Plugin plugin. If you are certain that you want the data to be deleted, please enable this toggle.',
							'wp-starter-plugin'
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
