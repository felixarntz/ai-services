/**
 * External dependencies
 */
import { store as pluginSettingsStore } from '@wp-starter-plugin/settings-store';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Renders the settings status text in a paragraph.
 *
 * @since n.e.x.t
 *
 * @returns The component to be rendered.
 */
export default function SettingsStatus() {
	const { isLoading, isDirty, isSaving } = useSelect( ( select ) => {
		const {
			getSettings,
			isResolving,
			hasModifiedSettings,
			isSavingSettings,
		} = select( pluginSettingsStore );

		return {
			isLoading:
				getSettings() === undefined || isResolving( 'getSettings' ),
			isDirty: hasModifiedSettings(),
			isSaving: isSavingSettings(),
		};
	}, [] );

	let statusText;
	if ( isLoading ) {
		statusText = __( 'Loading settings…', 'wp-starter-plugin' );
	} else if ( isSaving ) {
		statusText = __( 'Saving settings…', 'wp-starter-plugin' );
	} else if ( isDirty ) {
		statusText = __(
			'Some settings were modified and need to be saved.',
			'wp-starter-plugin'
		);
	} else {
		statusText = __( 'All settings are up to date.', 'wp-starter-plugin' );
	}

	return <p>{ statusText }</p>;
}
