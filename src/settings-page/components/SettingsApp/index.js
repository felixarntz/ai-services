/**
 * External dependencies
 */
import {
	App,
	Header,
	HeaderActions,
	Footer,
	PinnedSidebars,
} from '@wp-starter-plugin/interface';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SettingsShortcutsRegister from '../SettingsShortcutsRegister';
import SettingsShortcuts from '../SettingsShortcuts';
import UnsavedChangesWarning from '../UnsavedChangesWarning';
import SettingsSaveButton from '../SettingsSaveButton';
import SettingsMoreMenu from '../SettingsMoreMenu';
import SettingsCards from '../SettingsCards';
import SettingsStatus from '../SettingsStatus';

const interfaceLabels = {
	header: __( 'Settings top bar', 'wp-starter-plugin' ),
	body: __( 'Settings content', 'wp-starter-plugin' ),
	sidebar: __( 'Settings sidebar', 'wp-starter-plugin' ),
	actions: __( 'Settings actions', 'wp-starter-plugin' ),
	footer: __( 'Settings footer', 'wp-starter-plugin' ),
};

/**
 * Renders the full settings application.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function SettingsApp() {
	return (
		<App labels={ interfaceLabels }>
			<SettingsShortcutsRegister />
			<SettingsShortcuts />
			<UnsavedChangesWarning />
			<Header>
				<h1>{ __( 'Settings', 'wp-starter-plugin' ) }</h1>
				<HeaderActions>
					<SettingsSaveButton />
					<PinnedSidebars />
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
