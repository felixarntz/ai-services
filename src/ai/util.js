/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ContentRole } from './enums';

/**
 * Formats the various supported formats of new user content into a consistent content shape.
 *
 * @since 0.1.0
 *
 * @param {string|Object|Object[]} content New content.
 * @return {Object} The formatted new content.
 */
export function formatNewContent( content ) {
	if ( typeof content === 'string' ) {
		return {
			role: ContentRole.USER,
			parts: [ { text: content } ],
		};
	}

	if ( Array.isArray( content ) ) {
		// Could be an array of contents or parts.
		if ( content[ 0 ].role || content[ 0 ].parts ) {
			return content;
		}

		return {
			role: ContentRole.USER,
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
