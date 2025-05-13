/**
 * External dependencies
 */
import { store as pluginSettingsStore } from '@ai-services/settings';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Renders the settings status text in a paragraph.
 *
 * @since 0.1.0
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
		statusText = __( 'Loading settings…', 'ai-services' );
	} else if ( isSaving ) {
		statusText = __( 'Saving settings…', 'ai-services' );
	} else if ( isDirty ) {
		statusText = __(
			'Some settings were modified and need to be saved.',
			'ai-services'
		);
	} else {
		statusText = __( 'All settings are up to date.', 'ai-services' );
	}

	return <p>{ statusText }</p>;
}
