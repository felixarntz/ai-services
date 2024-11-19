/**
 * Processes a streaming response from the server, returning a generator that receives chunks of data.
 *
 * @since 0.3.0
 *
 * @param {Response} response The response object.
 * @return {Object} The generator that yields chunks of data.
 */
export default function processStream( response ) {
	const inputStream = response.body.pipeThrough(
		new TextDecoderStream( 'utf8', { fatal: true } )
	);
	const responseStream = getResponseStream( inputStream );
	return getResponseGenerator( responseStream );
}

/**
 * Gets a generator that yields chunks of data from a stream.
 *
 * @since 0.3.0
 *
 * @param {ReadableStream} stream The stream object.
 * @return {Object} The generator that yields chunks of data.
 */
async function* getResponseGenerator( stream ) {
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
 * @param {ReadableStream} inputStream The input stream.
 * @return {ReadableStream} The output stream.
 */
function getResponseStream( inputStream ) {
	const reader = inputStream.getReader();
	const stream = new ReadableStream( {
		/**
		 * Processes the input stream and enqueues chunks of data.
		 *
		 * @since 0.3.0
		 *
		 * @param {ReadableStreamDefaultController} controller The stream controller.
		 */
		start( controller ) {
			let buffer = '';

			reader.read().then( function processText( { value, done } ) {
				if ( done ) {
					if ( buffer ) {
						controller.enqueue( buffer );
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

				return reader.read().then( processText );
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
