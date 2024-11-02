/**
 * Converts a text string to a Content object.
 *
 * @since n.e.x.t
 *
 * @param {string} text The text.
 * @param {string} role Optional. The role to use for the content. Default 'user'.
 * @return {Object} The Content object.
 */
export function textToContent( text, role = 'user' ) {
	return {
		role,
		parts: [ { text } ],
	};
}

/**
 * Converts a Content object to a text string.
 *
 * This function will return the combined text from all consecutive text parts in the content.
 * Realistically, this should almost always return the text from just one part, as API responses typically do not
 * contain multiple text parts in a row - but it might be possible.
 *
 * @since n.e.x.t
 *
 * @param {Object} content The Content object.
 * @return {string} The text, or an empty string if there are no text parts.
 */
export function contentToText( content ) {
	const textParts = [];

	for ( const part of content.parts ) {
		/*
		 * If there is any non-text part present, we want to ensure that no interrupted text content is returned.
		 * Therefore, we break the loop as soon as we encounter a non-text part, unless no text parts have been
		 * found yet, in which case the text may only start with a later part.
		 */
		if ( part.text === undefined ) {
			if ( textParts.length > 0 ) {
				break;
			}
			continue;
		}

		textParts.push( part.text );
	}

	if ( textParts.length === 0 ) {
		return '';
	}

	return textParts.join( '\n\n' );
}

/**
 * Gets the text from the first Content object in the given list which contains text.
 *
 * @since n.e.x.t
 *
 * @param {Object[]} contents The list of Content objects.
 * @return {string} The text, or an empty string if no Content object has text parts.
 */
export function getTextFromContents( contents ) {
	for ( const content of contents ) {
		const text = contentToText( content );
		if ( text ) {
			return text;
		}
	}

	return '';
}

/**
 * Gets the Content objects for each candidate in the given list.
 *
 * @since n.e.x.t
 *
 * @param {Object[]} candidates The list of candidates.
 * @return {Object[]} The list of Content objects.
 */
export function getCandidateContents( candidates ) {
	const contents = [];

	for ( const candidate of candidates ) {
		if ( candidate.content ) {
			contents.push( candidate.content );
		}
	}

	return contents;
}
