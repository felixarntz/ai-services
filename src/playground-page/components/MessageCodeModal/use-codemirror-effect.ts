/**
 * External dependencies
 */
import type { MutableRefObject } from 'react';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';

type CodeMirrorStub = {
	toTextArea: () => void;
};

/**
 * React hook to initialize CodeMirror on a textarea.
 *
 * @param textareaRef - Reference to the textarea element.
 * @param language    - Language mode for CodeMirror.
 */
export default function useCodeMirrorEffect(
	textareaRef: MutableRefObject< HTMLTextAreaElement | null >,
	language: string
) {
	useEffect( () => {
		// @ts-expect-error Not worth setting this up properly for TypeScript.
		if ( ! wp?.CodeMirror || ! textareaRef.current ) {
			return;
		}

		const textarea = textareaRef.current;

		/*
		 * A timeout must be used to ensure the textarea is rendered before
		 * initializing CodeMirror. Otherwise, the textarea will not be
		 * initialized correctly.
		 */
		let codemirror: CodeMirrorStub | undefined;
		const timeout = setTimeout( () => {
			// @ts-expect-error Not worth setting this up properly for TypeScript.
			codemirror = wp.CodeMirror.fromTextArea( textarea, {
				indentUnit: 4,
				indentWithTabs: true,
				lineNumbers: true,
				lineWrapping: false,
				readOnly: !! textarea.readOnly,
				direction: 'ltr', // Code is shown in LTR even in RTL languages.
				mode: language,
			} ) as CodeMirrorStub;
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
