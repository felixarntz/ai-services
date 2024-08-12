/**
 * External dependencies
 */
import {
	App,
	Header,
	HeaderActions,
	Footer,
} from '@wp-oop-plugin-lib-example/components';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SettingsShortcutsRegister from '../SettingsShortcutsRegister';
import SettingsShortcuts from '../SettingsShortcuts';
import SettingsSaveButton from '../SettingsSaveButton';
import SettingsMoreMenu from '../SettingsMoreMenu';
import SettingsCards from '../SettingsCards';
import SettingsStatus from '../SettingsStatus';

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
			<SettingsShortcutsRegister />
			<SettingsShortcuts />
			<Header>
				<h1>{ __( 'Settings', 'wp-oop-plugin-lib-example' ) }</h1>
				<HeaderActions>
					<SettingsSaveButton />
					<SettingsMoreMenu />
				</HeaderActions>
			</Header>
			<SettingsCards />
			<Footer>
				<SettingsStatus />
			</Footer>
		</App>
	);
}
