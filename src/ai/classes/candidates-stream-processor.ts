/**
 * Internal dependencies
 */
import type { Content, Candidates, AsyncCandidatesGenerator } from '../types';

type CandidatesCallback = ( candidates: Candidates ) => void;

/**
 * Appends the content of a new candidate to the content of an existing candidate.
 *
 * @since 0.3.0
 *
 * @param existingContent - The existing content data.
 * @param newContent      - The new content data.
 * @returns The combined content data.
 */
function appendContent(
	existingContent: Content,
	newContent: Content
): Content {
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
			! ( 'text' in existingContent.parts[ index ] ) ||
			! ( 'text' in newPart )
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
 * @since 0.3.0
 */
export default class CandidatesStreamProcessor {
	generator: AsyncCandidatesGenerator;
	candidates: Candidates | null;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param generator - The generator that yields chunks of response candidates with the generated text content.
	 */
	constructor( generator: AsyncCandidatesGenerator ) {
		this.generator = generator;
		this.candidates = null;
	}

	/**
	 * Reads all chunks from the generator and adds them to the overall candidates instance.
	 *
	 * A callback can be passed that is called for each chunk of candidates. You could use such a callback for example
	 * to echo the text contents of each chunk as they are being processed.
	 *
	 * @since 0.3.0
	 *
	 * @param chunkCallback - Optional. Callback that is called for each chunk of candidates.
	 * @returns The complete candidates instance.
	 */
	async readAll( chunkCallback: CandidatesCallback | null = null ) {
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
	 * @since 0.3.0
	 *
	 * @param candidates - The chunk of candidates to add.
	 */
	addChunk( candidates: Candidates ) {
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
	 * @since 0.3.0
	 *
	 * @returns The complete candidates instance, or null if the generator is not done yet.
	 */
	getComplete(): Candidates | null {
		// TODO: How to check if the generator is done?
		return this.candidates;
	}
}
