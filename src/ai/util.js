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

/**
 * Performs some very basic client-side validation for the content argument.
 *
 * @since 0.1.0
 *
 * @param {string|Object|Object[]} content Content data to pass to the model, including the prompt and optional history.
 */
export function validateContent( content ) {
	if ( ! content ) {
		throw new Error(
			__(
				'The content argument is required to generate content.',
				'ai-services'
			)
		);
	}
	if ( ! Array.isArray( content ) ) {
		if ( typeof content === 'object' ) {
			if ( ! content.role || ! content.parts ) {
				throw new Error(
					__(
						'The content object must have a role and parts properties.',
						'ai-services'
					)
				);
			}
		} else if ( typeof content !== 'string' ) {
			throw new Error(
				__(
					'The content argument must be a string, an object, or an array of objects.',
					'ai-services'
				)
			);
		}
	}
}

/**
 * Validates the chat history.
 *
 * @since 0.1.0
 *
 * @param {Object[]} history Chat history.
 */
export function validateChatHistory( history ) {
	history.forEach( ( content, index ) => {
		if ( ! content.role || ! content.parts ) {
			throw new Error(
				__(
					'The content object must have a role and parts properties.',
					'ai-services'
				)
			);
		}

		if ( index === 0 && content.role !== ContentRole.USER ) {
			throw new Error(
				__(
					'The first content object in the history must be user content.',
					'ai-services'
				)
			);
		}

		if ( content.parts.length === 0 ) {
			throw new Error(
				__(
					'Each Content instance must have at least one part.',
					'ai-services'
				)
			);
		}
	} );
}
