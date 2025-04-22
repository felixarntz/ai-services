/**
 * Internal dependencies
 */
import CandidatesStreamProcessor from './classes/candidates-stream-processor';
import HistoryPersistence from './classes/history-persistence';
import { ContentRole } from './enums';

/**
 * Converts a text string to a Content object.
 *
 * @since 0.2.0
 *
 * @param {string} text The text.
 * @param {string} role Optional. The role to use for the content. Default 'user'.
 * @return {Object} The Content object.
 */
export function textToContent( text, role = ContentRole.USER ) {
	return {
		role,
		parts: [ { text } ],
	};
}

/**
 * Converts a text string and attachment to a multimodal Content instance.
 *
 * The text will be included as a prompt as the first part of the content, and the attachment (e.g. an image or
 * audio file) will be included as the second part.
 *
 * @since 0.5.0
 *
 * @param {string} text       The text.
 * @param {Object} attachment The attachment object.
 * @param {string} role       Optional. The role to use for the content. Default 'user'.
 * @return {Object} The Content object.
 */
export async function textAndAttachmentToContent(
	text,
	attachment,
	role = ContentRole.USER
) {
	return textAndAttachmentsToContent( text, [ attachment ], role );
}

/**
 * Converts a text string and an array of attachments to a multimodal Content instance.
 *
 * The text will be included as a prompt as the first part of the content, and the attachments (e.g. image or audio
 * files) will be included as the subsequent parts.
 *
 * @since 0.6.0
 *
 * @param {string}   text        The text.
 * @param {Object[]} attachments The attachment objects.
 * @param {string}   role        Optional. The role to use for the content. Default 'user'.
 * @return {Object} The Content object.
 */
export async function textAndAttachmentsToContent(
	text,
	attachments,
	role = ContentRole.USER
) {
	return {
		role,
		parts: [
			{ text },
			...( await Promise.all(
				attachments.map( async ( attachment ) => {
					const mimeType = attachment.mime;
					const data = await fileToBase64DataUrl(
						attachment.sizes?.large?.url || attachment.url
					);
					return { inlineData: { mimeType, data } };
				} )
			) ),
		],
	};
}

/**
 * Converts a Content object to a text string.
 *
 * This function will return the combined text from all consecutive text parts in the content.
 * Realistically, this should almost always return the text from just one part, as API responses typically do not
 * contain multiple text parts in a row - but it might be possible.
 *
 * @since 0.2.0
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
 * @since 0.2.0
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
 * Gets the first Content object in the given list which contains text.
 *
 * @since 0.5.0
 *
 * @param {Object[]} contents The list of Content objects.
 * @return {?Object} The Content object, or null if no Content object has text parts.
 */
export function getTextContentFromContents( contents ) {
	for ( const content of contents ) {
		const text = contentToText( content );
		if ( text ) {
			return content;
		}
	}

	return null;
}

/**
 * Gets the Content objects for each candidate in the given list.
 *
 * @since 0.2.0
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

/**
 * Processes a stream of candidates, aggregating the candidates chunks into a single candidates instance.
 *
 * This method returns a stream processor instance that can be used to read all chunks from the given candidates
 * generator and process them with a callback. Alternatively, you can read from the generator yourself and provide
 * all chunks to the processor manually.
 *
 * @since 0.3.0
 *
 * @param {Object} generator The generator that yields the chunks of response candidates.
 * @return {CandidatesStreamProcessor} The stream processor instance.
 */
export function processCandidatesStream( generator ) {
	return new CandidatesStreamProcessor( generator );
}

let historyPersistenceInstance;

/**
 * Gets the history persistence instance, to load, save, and clear histories.
 *
 * @since 0.5.0
 *
 * @return {HistoryPersistence} The history persistence instance.
 */
export function historyPersistence() {
	if ( ! historyPersistenceInstance ) {
		historyPersistenceInstance = new HistoryPersistence();
	}

	return historyPersistenceInstance;
}

/**
 * Returns the base64-encoded data URL representation of the given file URL.
 *
 * @since 0.5.0
 *
 * @param {string} file     The file URL.
 * @param {string} mimeType Optional. The MIME type of the file. If provided, the base64-encoded data URL will
 *                          be prefixed with `data:{mime_type};base64,`. Default empty string.
 * @return {string} The base64-encoded file data URL, or empty string on failure.
 */
export async function fileToBase64DataUrl( file, mimeType = '' ) {
	const blob = await fileToBlob( file, mimeType );
	if ( ! blob ) {
		return '';
	}

	return blobToBase64DataUrl( blob );
}

/**
 * Returns the binary data blob representation of the given file URL.
 *
 * @since 0.5.0
 *
 * @param {string} file     The file URL.
 * @param {string} mimeType Optional. The MIME type of the file. If provided, the automatically detected MIME type will
 *                          be overwritten. Default empty string.
 * @return {Blob?} The binary data blob, or null on failure.
 */
export async function fileToBlob( file, mimeType = '' ) {
	const data = await fetch( file );
	const blob = await data.blob();
	if ( ! blob ) {
		return null;
	}
	if ( mimeType && mimeType !== blob.type ) {
		return new Blob( [ blob ], { type: mimeType } );
	}
	return blob;
}

/**
 * Returns the base64-encoded data URL representation of the given binary data blob.
 *
 * @since 0.5.0
 *
 * @param {Blob} blob The binary data blob.
 * @return {string} The base64-encoded data URL, or empty string on failure.
 */
export async function blobToBase64DataUrl( blob ) {
	const base64DataUrl = await new Promise( ( resolve ) => {
		const reader = new window.FileReader();
		reader.readAsDataURL( blob );
		reader.onloadend = () => {
			const base64data = reader.result;
			resolve( base64data );
		};
	} );

	return base64DataUrl;
}

/**
 * Returns the binary data blob representation of the given base64-encoded data URL.
 *
 * @since 0.5.0
 *
 * @param {string} base64DataUrl The base64-encoded data URL.
 * @return {Blob?} The binary data blob, or null on failure.
 */
export async function base64DataUrlToBlob( base64DataUrl ) {
	const prefixMatch = base64DataUrl.match(
		/^data:([a-z0-9-]+\/[a-z0-9-]+);base64,/
	);
	if ( ! prefixMatch ) {
		return null;
	}

	const base64Data = base64DataUrl.substring( prefixMatch[ 0 ].length );
	const binaryData = atob( base64Data );
	const byteArrays = [];

	for ( let offset = 0; offset < binaryData.length; offset += 512 ) {
		const slice = binaryData.slice( offset, offset + 512 );

		const byteNumbers = new Array( slice.length );
		for ( let i = 0; i < slice.length; i++ ) {
			byteNumbers[ i ] = slice.charCodeAt( i );
		}
		byteArrays.push( new Uint8Array( byteNumbers ) );
	}

	return new Blob( byteArrays, {
		type: prefixMatch[ 1 ],
	} );
}

/**
 * Ensures the given base64 data is prefixed correctly to be a data URL.
 *
 * @since 0.6.0
 *
 * @param {string} base64Data Base64-encoded data. If it is already a data URL, it will be returned as is.
 * @param {string} mimeType   MIME type for the data.
 * @return {string} The base64 data URL.
 */
export function base64DataToBase64DataUrl( base64Data, mimeType ) {
	if ( base64Data.startsWith( 'data:' ) ) {
		return base64Data;
	}
	return `data:${ mimeType };base64,${ base64Data }`;
}

/**
 * Ensures the given base64 data URL has its prefix removed to be just the base64 data.
 *
 * @since 0.6.0
 *
 * @param {string} base64DataUrl Base64 data URL. If it is already without prefix, it will be returned as is.
 * @return {string} The base64-encoded data.
 */
export function base64DataUrlToBase64Data( base64DataUrl ) {
	if ( ! base64DataUrl.startsWith( 'data:' ) ) {
		return base64DataUrl;
	}
	return base64DataUrl.replace( /^data:[a-z0-9-]+\/[a-z0-9-]+;base64,/, '' );
}
