/**
 * External dependencies
 */
import { store as pluginSettingsStore } from '@wp-starter-plugin/settings-store';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Renders the Save button to display in the header of the settings app.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function SettingsSaveButton() {
	const { isSaving, isSaveable } = useSelect( ( select ) => {
		const { isSavingSettings, areSettingsSaveable } =
			select( pluginSettingsStore );

		return {
			isSaving: isSavingSettings(),
			isSaveable: areSettingsSaveable(),
		};
	} );

	const { saveSettings } = useDispatch( pluginSettingsStore );

	return (
		<Button
			variant="primary"
			onClick={ saveSettings }
			isBusy={ isSaving }
			aria-disabled={ ! isSaveable }
			// The prop accessibleWhenDisabled should be used here, but doesn't work.
			__next40pxDefaultSize
		>
			{ __( 'Save', 'wp-starter-plugin' ) }
		</Button>
	);
}
