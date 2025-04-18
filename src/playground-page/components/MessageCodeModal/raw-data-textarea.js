/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useCodeMirrorEffect from './use-codemirror-effect';

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

	const textareaRef = useRef();

	// Initialize 'wp-codemirror'.
	useCodeMirrorEffect( textareaRef, 'javascript' );

	return (
		<div className="ai-services-playground__code-textarea-wrapper">
			<textarea
				ref={ textareaRef }
				className="ai-services-playground__code-textarea code"
				aria-label={ __(
					'Raw data for the selected message',
					'ai-services'
				) }
				value={ rawDataJson }
				rows="14"
				readOnly
			/>
		</div>
	);
}
