/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

/**
 * React hook to initialize CodeMirror on a textarea.
 *
 * @param {Object} textareaRef Reference to the textarea element.
 * @param {string} language    Language mode for CodeMirror.
 */
export default function useCodeMirrorEffect( textareaRef, language ) {
	useEffect( () => {
		if ( ! wp?.CodeMirror || ! textareaRef.current ) {
			return;
		}

		const textarea = textareaRef.current;

		/*
		 * A timeout must be used to ensure the textarea is rendered before
		 * initializing CodeMirror. Otherwise, the textarea will not be
		 * initialized correctly.
		 */
		let codemirror;
		const timeout = setTimeout( () => {
			codemirror = wp.CodeMirror.fromTextArea( textarea, {
				indentUnit: 4,
				indentWithTabs: true,
				lineNumbers: true,
				lineWrapping: false,
				readOnly: !! textarea.readonly,
				direction: 'ltr', // Code is shown in LTR even in RTL languages.
				mode: language,
			} );
		}, 0 );

		return () => {
			clearTimeout( timeout );

			// Note: A parent node must be present, otherwise CodeMirror will throw an error.
			if ( codemirror && textarea.parentNode ) {
				codemirror.toTextArea();
			}
		};
	}, [ textareaRef, language ] );
}
