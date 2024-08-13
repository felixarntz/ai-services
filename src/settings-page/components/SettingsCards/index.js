/**
 * External dependencies
 */
import { store as pluginStore } from '@wp-oop-plugin-lib-example/store';

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
 * @return {Component} The component to be rendered.
 */
export default function SettingsCards() {
	const { isLoading, deleteData } = useSelect( ( select ) => {
		const { getSettings, isResolving, getDeleteData } =
			select( pluginStore );

		return {
			isLoading:
				getSettings() === undefined || isResolving( 'getSettings' ),
			deleteData: getDeleteData(),
		};
	} );

	const { setDeleteData } = useDispatch( pluginStore );

	return (
		<div className="wpoopple-settings-cards">
			<Card>
				<CardHeader>
					<h2 className="wpoopple-settings-cards__heading">
						{ __( 'Advanced', 'wp-oop-plugin-lib-example' ) }
					</h2>
				</CardHeader>
				<CardBody>
					<ToggleControl
						label={ __(
							'Delete plugin data upon uninstallation',
							'wp-oop-plugin-lib-example'
						) }
						help={ __(
							'By default no data will be deleted, should you decide to uninstall the WP OOP Plugin Lib Example plugin. If you are certain that you want the data to be deleted, please enable this toggle.',
							'wp-oop-plugin-lib-example'
						) }
						disabled={ isLoading }
						checked={ deleteData || false }
						onChange={ setDeleteData }
					/>
				</CardBody>
			</Card>
		</div>
	);
}
