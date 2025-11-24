/**
 * External dependencies
 */
import { PluginIcon } from '@ai-services/components';
import {
	App,
	Header,
	HeaderActions,
	Footer,
	PinnedSidebars,
} from 'wp-interface';

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
import './style.scss';

/**
 * Renders the full settings application.
 *
 * @since 0.1.0
 *
 * @returns The component to be rendered.
 */
export default function SettingsApp() {
	const labels = {
		header: __( 'Settings top bar', 'ai-services' ),
		body: __( 'Settings content', 'ai-services' ),
		sidebar: __( 'Settings sidebar', 'ai-services' ),
		actions: __( 'Settings actions', 'ai-services' ),
		footer: __( 'Settings footer', 'ai-services' ),
		keyboardShortcutsModalTitle: __( 'Keyboard shortcuts', 'ai-services' ),
		keyboardShortcutsModalCloseButtonLabel: __(
			'Close keyboard shortcuts modal',
			'ai-services'
		),
		keyboardShortcutsGlobalSectionTitle: __(
			'Global shortcuts',
			'ai-services'
		),
	};

	const shortcutsDescriptions = {
		'keyboard-shortcuts': __(
			'Display these keyboard shortcuts.',
			'ai-services'
		),
		'next-region': __(
			'Navigate to the next part of the screen.',
			'ai-services'
		),
		'previous-region': __(
			'Navigate to the previous part of the screen.',
			'ai-services'
		),
		'toggle-distraction-free': __(
			'Toggle distraction free mode.',
			'ai-services'
		),
		'toggle-sidebar': __( 'Show or hide the sidebar.', 'ai-services' ),
	};

	return (
		<App
			scope="ai-services"
			labels={ labels }
			shortcutsDescriptions={ shortcutsDescriptions }
		>
			<SettingsShortcutsRegister />
			<SettingsShortcuts />
			<UnsavedChangesWarning />
			<Header>
				<PluginIcon size={ 48 } />
				<h1>
					{ __( 'AI Services', 'ai-services' ) }
					{ ': ' }
					{ __( 'Settings', 'ai-services' ) }
				</h1>
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
