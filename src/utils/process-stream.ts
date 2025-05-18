/**
 * Processes a streaming response from the server, returning a generator that receives chunks of data.
 *
 * @since 0.3.0
 *
 * @param response - The response object.
 * @returns The generator that yields chunks of data.
 */
export default function processStream< T >(
	response: Response
): AsyncGenerator< T, void, void > {
	if ( response.body === null ) {
		throw new Error(
			'Response body is null. This may be due to a network error or an unsupported response type.'
		);
	}
	const inputStream = response.body.pipeThrough(
		new TextDecoderStream( 'utf8', { fatal: true } )
	);
	const responseStream = getResponseStream< T >( inputStream );
	return getResponseGenerator< T >( responseStream );
}

/**
 * Gets a generator that yields chunks of data from a stream.
 *
 * @since 0.3.0
 *
 * @param stream - The stream object.
 * @returns The generator that yields chunks of data.
 */
export async function* getResponseGenerator< T >(
	stream: ReadableStream< T >
): AsyncGenerator< T, void, void > {
	const reader = stream.getReader();
	while ( true ) {
		const { value, done } = await reader.read();
		if ( done ) {
			break;
		}
		yield value;
	}
}

const chunkLineRegex = /^data\: (.*)(?:\n\n|\r\r|\r\n\r\n)/;

/**
 * Gets a stream that processes chunks of data from an input stream.
 *
 * The input stream is expected to come from a 'text/event-stream' response, containing JSON-encoded chunks.
 *
 * Each chunk is parsed and yielded as an object. If parsing fails, an error is logged and the chunk is skipped.
 * The stream is closed when the input stream is closed, and cancelled when the input stream is cancelled.
 *
 * @since 0.3.0
 *
 * @param inputStream - The input stream.
 * @returns The output stream.
 */
function getResponseStream< T >(
	inputStream: ReadableStream< string >
): ReadableStream< T > {
	const reader = inputStream.getReader();
	const stream = new ReadableStream< T >( {
		/**
		 * Processes the input stream and enqueues chunks of data.
		 *
		 * @since 0.3.0
		 *
		 * @param controller - The stream controller.
		 */
		start( controller ) {
			let buffer = '';

			reader.read().then( function processText( { value, done } ): void {
				if ( done ) {
					if ( buffer ) {
						// TODO: May this still contain JSON to parse?
						// controller.enqueue( buffer );
					}
					controller.close();
					return;
				}

				buffer += value;

				let match = buffer.match( chunkLineRegex );
				let chunk;
				while ( match ) {
					try {
						chunk = JSON.parse( match[ 1 ] );
					} catch ( error ) {
						window.console.error(
							`Error parsing JSON: ${ match[ 1 ] }`
						);
						return;
					}

					controller.enqueue( chunk );
					buffer = buffer.substring( match[ 0 ].length );
					match = buffer.match( chunkLineRegex );
				}

				reader.read().then( processText );
			} );
		},

		/**
		 * Cancels the input stream.
		 *
		 * @since 0.3.0
		 */
		cancel() {
			reader.cancel();
		},
	} );
	return stream;
}
