/**
 * External dependencies
 */
import { App } from '@wp-oop-plugin-lib-example/components';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SettingsHeader from '../SettingsHeader';
import SettingsCards from '../SettingsCards';
import SettingsFooter from '../SettingsFooter';

const interfaceLabels = {
	header: __( 'Settings top bar', 'wp-oop-plugin-lib-example' ),
	body: __( 'Settings content', 'wp-oop-plugin-lib-example' ),
	sidebar: __( 'Settings sidebar', 'wp-oop-plugin-lib-example' ),
	actions: __( 'Settings actions', 'wp-oop-plugin-lib-example' ),
	footer: __( 'Settings footer', 'wp-oop-plugin-lib-example' ),
};

export default function SettingsApp() {
	return (
		<App labels={ interfaceLabels }>
			<SettingsHeader />
			<SettingsCards />
			<SettingsFooter />
		</App>
	);
}
