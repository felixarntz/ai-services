/**
 * WordPress dependencies
 */
import {
	Card,
	CardHeader,
	CardBody,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

export default function SettingsCards() {
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
						checked={ false }
						onChange={ ( newValue ) => {
							window.console.log( newValue );
						} }
					/>
				</CardBody>
			</Card>
		</div>
	);
}
