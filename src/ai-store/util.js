/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Formats the various supported formats of new user content into a consistent content shape.
 *
 * @since n.e.x.t
 *
 * @param {string|Object|Object[]} content New content.
 * @return {Object} The formatted new content.
 */
export function formatNewContent( content ) {
	if ( typeof content === 'string' ) {
		return {
			role: 'user',
			parts: [ { text: content } ],
		};
	}

	if ( Array.isArray( content ) ) {
		// Could be an array of contents or parts.
		if ( content[ 0 ].role || content[ 0 ].parts ) {
			return content;
		}

		return {
			role: 'user',
			parts: content,
		};
	}

	if ( ! content.role || ! content.parts ) {
		throw new Error(
			__(
				'The value must be a string, a parts object, or a content object.',
				'ai-services'
			)
		);
	}

	return content;
}
