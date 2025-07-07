<?php
/**
 * Class Felix_Arntz\AI_Services\Services\API\Candidates_Stream_Processor
 *
 * @since 0.3.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\API;

use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Generator;

/**
 * Class to process a candidates stream.
 *
 * @since 0.3.0
 */
final class Candidates_Stream_Processor {

	/**
	 * Generator that yields the chunks of response candidates.
	 *
	 * @since 0.3.0
	 * @var Generator<Candidates>
	 */
	private $generator;

	/**
	 * The overall candidates instance.
	 *
	 * May be incomplete if the stream has not been fully processed yet.
	 *
	 * @since 0.3.0
	 * @var Candidates|null
	 */
	private $candidates;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param Generator<Candidates> $generator The generator that yields the chunks of response candidates.
	 */
	public function __construct( Generator $generator ) { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint
		$this->generator = $generator;
	}

	/**
	 * Reads all chunks from the generator and adds them to the overall candidates instance.
	 *
	 * A callback can be passed that is called for each chunk of candidates. You could use such a callback for example
	 * to echo the text contents of each chunk as they are being processed.
	 *
	 * @since 0.3.0
	 *
	 * @param callable|null $chunk_callback Optional. Callback that is called for each chunk of candidates.
	 * @return Candidates The complete candidates instance.
	 */
	public function read_all( ?callable $chunk_callback = null ): Candidates {
		foreach ( $this->generator as $candidates ) {
			$this->add_chunk( $candidates );
			if ( null !== $chunk_callback ) {
				$chunk_callback( $candidates );
			}
		}
		return $this->get_complete();
	}

	/**
	 * Adds a chunk of candidates to the overall candidates instance.
	 *
	 * @since 0.3.0
	 *
	 * @param Candidates $candidates The chunk of candidates to add.
	 */
	public function add_chunk( Candidates $candidates ): void {
		if ( null === $this->candidates ) {
			$this->candidates = $candidates;
			return;
		}

		$existing_candidates = $this->candidates->to_array();
		$new_candidates      = $candidates->to_array();

		foreach ( $new_candidates as $index => $new_candidate ) {
			if ( ! isset( $existing_candidates[ $index ] ) ) {
				$existing_candidates[] = $new_candidate;
				continue;
			}

			if ( isset( $existing_candidates[ $index ]['content'] ) && isset( $new_candidate['content'] ) ) {
				$existing_candidates[ $index ]['content'] = $this->append_content(
					$existing_candidates[ $index ]['content'],
					$new_candidate['content']
				);
				unset( $new_candidate['content'] );
			}

			$existing_candidates[ $index ] = array_merge( $existing_candidates[ $index ], $new_candidate );
		}

		$this->candidates = Candidates::from_array( $existing_candidates );
	}

	/**
	 * Gets the complete candidates instance.
	 *
	 * @since 0.3.0
	 *
	 * @return Candidates|null The complete candidates instance, or null if the generator is not done yet.
	 */
	public function get_complete(): ?Candidates {
		// Only return the candidates if the generator is done.
		if ( $this->generator->valid() ) {
			return null;
		}
		return $this->candidates;
	}

	/**
	 * Appends the content of a new candidate to the content of an existing candidate.
	 *
	 * @since 0.3.0
	 *
	 * @param array<string, mixed> $existing_content The existing content data.
	 * @param array<string, mixed> $new_content      The new content data.
	 * @return array<string, mixed> The combined content data.
	 */
	private function append_content( array $existing_content, array $new_content ) {
		if ( ! isset( $existing_content['parts'] ) || ! isset( $new_content['parts'] ) ) {
			return $existing_content;
		}

		foreach ( $new_content['parts'] as $index => $new_part ) {
			if ( ! isset( $existing_content['parts'][ $index ] ) ) {
				$existing_content['parts'][] = $new_part;
				continue;
			}

			if ( ! isset( $existing_content['parts'][ $index ]['text'] ) || ! isset( $new_part['text'] ) ) {
				continue;
			}

			$existing_content['parts'][ $index ]['text'] .= $new_part['text'];
		}

		return $existing_content;
	}
}
