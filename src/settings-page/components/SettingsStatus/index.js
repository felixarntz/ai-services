/**
 * External dependencies
 */
import { store as pluginStore } from '@wp-oop-plugin-lib-example/store';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

export default function SettingsStatus() {
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

	let statusText;
	if ( isLoading ) {
		statusText = __( 'Loading settings…', 'wp-oop-plugin-lib-example' );
	} else if ( isSaving ) {
		statusText = __( 'Saving settings…', 'wp-oop-plugin-lib-example' );
	} else if ( isDirty ) {
		statusText = __(
			'Some settings were modified and need to be saved.',
			'wp-oop-plugin-lib-example'
		);
	} else {
		statusText = __(
			'All settings are up to date.',
			'wp-oop-plugin-lib-example'
		);
	}

	return <p>{ statusText }</p>;
}
