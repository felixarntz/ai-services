/**
 * External dependencies
 */
import { store as pluginStore } from '@wp-oop-plugin-lib-example/store';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export default function SettingsSaveButton() {
	const { isSaving, isSaveable } = useSelect( ( select ) => {
		const { isSavingSettings, areSettingsSaveable } = select( pluginStore );

		return {
			isSaving: isSavingSettings(),
			isSaveable: areSettingsSaveable(),
		};
	} );

	const { saveSettings } = useDispatch( pluginStore );

	return (
		<Button
			variant="primary"
			onClick={ saveSettings }
			isBusy={ isSaving }
			aria-disabled={ ! isSaveable }
			// The prop accessibleWhenDisabled should be used here, but doesn't work.
		>
			{ __( 'Save', 'wp-oop-plugin-lib-example' ) }
		</Button>
	);
}
