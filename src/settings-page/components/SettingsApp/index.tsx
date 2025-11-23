/**
 * External dependencies
 */
import {
	App,
	Header,
	HeaderActions,
	Footer,
	PinnedSidebars,
} from '@felixarntz/wp-interface';

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

/**
 * Renders the full settings application.
 *
 * @since n.e.x.t
 *
 * @returns The component to be rendered.
 */
export default function SettingsApp() {
	const labels = {
		header: __( 'Settings top bar', 'wp-starter-plugin' ),
		body: __( 'Settings content', 'wp-starter-plugin' ),
		sidebar: __( 'Settings sidebar', 'wp-starter-plugin' ),
		actions: __( 'Settings actions', 'wp-starter-plugin' ),
		footer: __( 'Settings footer', 'wp-starter-plugin' ),
		keyboardShortcutsModalTitle: __(
			'Keyboard shortcuts',
			'wp-starter-plugin'
		),
		keyboardShortcutsModalCloseButtonLabel: __(
			'Close keyboard shortcuts modal',
			'wp-starter-plugin'
		),
		keyboardShortcutsGlobalSectionTitle: __(
			'Global shortcuts',
			'wp-starter-plugin'
		),
	};

	const shortcutsDescriptions = {
		'keyboard-shortcuts': __(
			'Display these keyboard shortcuts.',
			'wp-starter-plugin'
		),
		'next-region': __(
			'Navigate to the next part of the screen.',
			'wp-starter-plugin'
		),
		'previous-region': __(
			'Navigate to the previous part of the screen.',
			'wp-starter-plugin'
		),
		'toggle-distraction-free': __(
			'Toggle distraction free mode.',
			'wp-starter-plugin'
		),
		'toggle-sidebar': __(
			'Show or hide the sidebar.',
			'wp-starter-plugin'
		),
	};

	return (
		<App
			scope="wp-starter-plugin"
			labels={ labels }
			shortcutsDescriptions={ shortcutsDescriptions }
		>
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
