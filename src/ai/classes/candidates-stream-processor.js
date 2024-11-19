/**
 * Appends the content of a new candidate to the content of an existing candidate.
 *
 * @since n.e.x.t
 *
 * @param {Object} existingContent The existing content data.
 * @param {Object} newContent      The new content data.
 * @return {Object} The combined content data.
 */
function appendContent( existingContent, newContent ) {
	existingContent = {
		...existingContent,
		parts: [ ...( existingContent.parts || [] ) ],
	};

	if ( ! existingContent.parts || ! newContent.parts ) {
		return existingContent;
	}

	newContent.parts.forEach( ( newPart, index ) => {
		if ( ! existingContent.parts[ index ] ) {
			existingContent.parts.push( { ...newPart } );
			return;
		}

		if (
			existingContent.parts[ index ].text === undefined ||
			newPart.text === undefined
		) {
			return;
		}

		existingContent.parts[ index ] = {
			text: existingContent.parts[ index ].text + newPart.text,
		};
	} );

	return existingContent;
}

/**
 * Class to process a candidates stream.
 *
 * @since n.e.x.t
 */
export default class CandidatesStreamProcessor {
	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param {Object} generator The generator that yields chunks of response candidates with the generated text
	 *                           content.
	 */
	constructor( generator ) {
		this.generator = generator;
		this.candidates = null;
	}

	/**
	 * Reads all chunks from the generator and adds them to the overall candidates instance.
	 *
	 * A callback can be passed that is called for each chunk of candidates. You could use such a callback for example
	 * to echo the text contents of each chunk as they are being processed.
	 *
	 * @since n.e.x.t
	 *
	 * @param {Function|null} chunkCallback Optional. Callback that is called for each chunk of candidates.
	 * @return {Promise<Object[]>} The complete candidates instance.
	 */
	async readAll( chunkCallback ) {
		for await ( const candidates of this.generator ) {
			this.addChunk( candidates );

			if ( chunkCallback ) {
				chunkCallback( candidates );
			}
		}
		return this.getComplete();
	}

	/**
	 * Adds a chunk of candidates to the overall candidates instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param {Object} candidates The chunk of candidates to add.
	 */
	addChunk( candidates ) {
		if ( ! this.candidates ) {
			this.candidates = candidates;
			return;
		}

		const existingCandidates = [ ...this.candidates ];
		const newCandidates = [ ...candidates ];

		newCandidates.forEach( ( newCandidate, index ) => {
			if ( ! existingCandidates[ index ] ) {
				existingCandidates.push( { ...newCandidate } );
				return;
			}

			if ( existingCandidates[ index ].content && newCandidate.content ) {
				const updatedContent = appendContent(
					existingCandidates[ index ].content,
					newCandidate.content
				);
				newCandidate = { ...newCandidate, content: updatedContent };
			}

			existingCandidates[ index ] = {
				...existingCandidates[ index ],
				...newCandidate,
			};
		} );

		this.candidates = existingCandidates;
	}

	/**
	 * Gets the complete candidates instance.
	 *
	 * @since n.e.x.t
	 *
	 * @return {Object[]|null} The complete candidates instance, or null if the generator is not done yet.
	 */
	getComplete() {
		// TODO: How to check if the generator is done?
		return this.candidates;
	}
}
