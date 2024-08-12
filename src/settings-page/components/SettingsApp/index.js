/**
 * External dependencies
 */
import { App } from '@wp-oop-plugin-lib-example/components';
import { store as pluginStore } from '@wp-oop-plugin-lib-example/store';

/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useShortcut } from '@wordpress/keyboard-shortcuts';

/**
 * Internal dependencies
 */
import SettingsShortcutsRegister from '../SettingsShortcutsRegister';
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
	const { saveSettings } = useDispatch( pluginStore );

	const handleSave = ( event ) => {
		event.preventDefault();
		saveSettings();
	};
	useShortcut( 'wp-oop-plugin-lib-example/save', handleSave );

	return (
		<App labels={ interfaceLabels }>
			<SettingsShortcutsRegister />
			<SettingsHeader />
			<SettingsCards />
			<SettingsFooter />
		</App>
	);
}
