/**
 * External dependencies
 */
import {
	App,
	Header,
	HeaderActions,
	Footer,
	PinnedSidebars,
} from '@ai-services/components';

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
	header: __( 'Settings top bar', 'ai-services' ),
	body: __( 'Settings content', 'ai-services' ),
	sidebar: __( 'Settings sidebar', 'ai-services' ),
	actions: __( 'Settings actions', 'ai-services' ),
	footer: __( 'Settings footer', 'ai-services' ),
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
				<h1>{ __( 'Settings', 'ai-services' ) }</h1>
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
