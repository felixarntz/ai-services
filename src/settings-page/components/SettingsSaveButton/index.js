/**
 * External dependencies
 */
import { store as pluginStore } from '@wp-oop-plugin-lib-example/store';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { __ } from '@wordpress/i18n';

const SAVE_SETTINGS_NOTICE_ID = 'SAVE_SETTINGS_NOTICE_ID';

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

	const isDisabled = isLoading || ! isDirty || isSaving;

	const { saveSettings } = useDispatch( pluginStore );
	const { createErrorNotice, createSuccessNotice } =
		useDispatch( noticesStore );

	const handleSave = async () => {
		if ( isDisabled ) {
			return;
		}

		try {
			await saveSettings();
		} catch ( error ) {
			console.error( error ); // eslint-disable-line no-console
			createErrorNotice(
				__( 'Saving settings failed.', 'wp-oop-plugin-lib-example' ),
				{
					id: SAVE_SETTINGS_NOTICE_ID,
					type: 'snackbar',
					speak: true,
				}
			);
			return;
		}

		createSuccessNotice(
			__( 'Settings successfully saved.', 'wp-oop-plugin-lib-example' ),
			{
				id: SAVE_SETTINGS_NOTICE_ID,
				type: 'snackbar',
				speak: true,
			}
		);
	};

	return (
		<Button
			variant="primary"
			onClick={ handleSave }
			isBusy={ isSaving }
			aria-disabled={ isDisabled }
			// The prop accessibleWhenDisabled should be used here, but doesn't work.
		>
			{ __( 'Save', 'wp-oop-plugin-lib-example' ) }
		</Button>
	);
}
