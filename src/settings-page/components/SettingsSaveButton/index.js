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
	const { isLoading, isDirty, isSaving } = useSelect( ( select ) => {
		const {
			getSettings,
			isResolving,
			hasModifiedSettings,
			isSavingSettings,
		} = select( pluginStore );

		return {
			isLoading:
				getSettings() === undefined || isResolving( 'getSettings' ),
			isDirty: hasModifiedSettings(),
			isSaving: isSavingSettings(),
		};
	} );

	const { saveSettings } = useDispatch( pluginStore );

	const handleSave = async () => {
		await saveSettings();

		// TODO: Trigger snackbar notice.
	};

	const isDisabled = isLoading || ! isDirty || isSaving;

	return (
		<Button
			variant="primary"
			onClick={ handleSave }
			disabled={ isDisabled }
			isBusy={ isSaving }
			accessibleWhenDisabled={ true }
		>
			{ __( 'Save', 'wp-oop-plugin-lib-example' ) }
		</Button>
	);
}
