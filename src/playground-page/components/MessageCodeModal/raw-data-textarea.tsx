/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useCodeMirrorEffect from './use-codemirror-effect';
import type { AiPlaygroundMessageAdditionalData } from '../../types';

type RawDataTextareaProps = {
	rawData: Exclude<
		AiPlaygroundMessageAdditionalData[ 'rawData' ],
		undefined
	>;
};

/**
 * Renders a textarea with the raw data for the selected message.
 *
 * @since 0.6.0
 *
 * @param props - The component properties.
 * @returns The component to be rendered.
 */
export default function RawDataTextarea( props: RawDataTextareaProps ) {
	const { rawData } = props;

	const rawDataJson = useMemo( () => {
		return JSON.stringify( rawData, null, 2 );
	}, [ rawData ] );

	const textareaRef = useRef< HTMLTextAreaElement >( null );

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
				rows={ 14 }
				readOnly
			/>
		</div>
	);
}
