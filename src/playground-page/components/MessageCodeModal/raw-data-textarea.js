/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';

/**
 * Renders a textarea with the raw data for the selected message.
 *
 * @since n.e.x.t
 *
 * @param {Object} props         The component properties.
 * @param {Object} props.rawData The raw data for the selected message.
 * @return {Component} The component to be rendered.
 */
export default function RawDataTextarea( { rawData } ) {
	const rawDataJson = useMemo( () => {
		return JSON.stringify( rawData, null, 2 );
	}, [ rawData ] );

	return (
		<textarea
			className="ai-services-playground__code-textarea code"
			aria-label={ __(
				'Raw data for the selected message',
				'ai-services'
			) }
			value={ rawDataJson }
			rows="14"
			readOnly
		/>
	);
}
