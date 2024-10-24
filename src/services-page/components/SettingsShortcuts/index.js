/**
 * External dependencies
 */
import { store as pluginSettingsStore } from '@ai-services/settings';

/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';
import { useShortcut } from '@wordpress/keyboard-shortcuts';

/**
 * Renders a utility component to add event listeners for keyboard shortcuts for the settings app.
 *
 * @since 0.1.0
 *
 * @return {Component} The component to be rendered.
 */
export default function SettingsShortcuts() {
	const { saveSettings } = useDispatch( pluginSettingsStore );

	const handleSave = ( event ) => {
		event.preventDefault();
		saveSettings();
	};
	useShortcut( 'ai-services/save', handleSave );

	return null;
}
