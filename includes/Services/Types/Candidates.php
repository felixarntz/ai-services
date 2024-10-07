<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Types\Candidates
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Types;

use ArrayIterator;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Arrayable;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Collection;
use InvalidArgumentException;
use Traversable;

/**
 * Class representing a collection of response candidates for a generative model.
 *
 * @since 0.1.0
 */
final class Candidates implements Collection, Arrayable {

	/**
	 * The candidates.
	 *
	 * @since 0.1.0
	 * @var Candidate[]
	 */
	private $candidates = array();

	/**
	 * Adds a candidate to the collection.
	 *
	 * @since 0.1.0
	 *
	 * @param Candidate $candidate The candidate.
	 */
	public function add_candidate( Candidate $candidate ): void {
		$this->candidates[] = $candidate;
	}

	/**
	 * Returns an iterator for the candidates collection.
	 *
	 * @since 0.1.0
	 *
	 * @return ArrayIterator<int, Candidate> Collection iterator.
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->candidates );
	}

	/**
	 * Returns the size of the candidates collection.
	 *
	 * @since 0.1.0
	 *
	 * @return int Collection size.
	 */
	public function count(): int {
		return count( $this->candidates );
	}

	/**
	 * Filters the parts collection by the given criteria.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $args {
	 *     The filter arguments.
	 *
	 *     @type string $part_class_name The class name to only allow candidates with content parts of that class.
	 * }
	 * @return Candidates The filtered parts collection.
	 */
	public function filter( array $args ): self {
		if ( isset( $args['part_class_name'] ) ) {
			$part_class_name = $args['part_class_name'];
			$map             = static function ( Candidate $candidate ) use ( $part_class_name ) {
				$filtered_parts = $candidate->get_content()->get_parts()->filter( array( 'class_name' => $part_class_name ) );
				if ( count( $filtered_parts ) > 0 ) {
					$candidate_data                     = $candidate->to_array();
					$candidate_data['content']['parts'] = $filtered_parts->to_array();
					return Candidate::from_array( $candidate_data );
				}
				return null;
			};
		} else {
			$map = static function ( Candidate $candidate ) {
				return Candidate::from_array( $candidate->to_array() );
			};
		}

		$candidates = new Candidates();
		foreach ( $this->candidates as $candidate ) {
			$mapped_candidate = $map( $candidate );
			if ( $mapped_candidate ) {
				$candidates->add_candidate( $mapped_candidate );
			}
		}
		return $candidates;
	}

	/**
	 * Returns the candidate at the given index.
	 *
	 * @since 0.1.0
	 *
	 * @param int $index The index.
	 * @return Candidate The candidate.
	 *
	 * @throws InvalidArgumentException Thrown if the index is out of bounds.
	 */
	public function get( int $index ): Candidate {
		if ( ! isset( $this->candidates[ $index ] ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'Index out of bounds.', 'ai-services' )
			);
		}
		return $this->candidates[ $index ];
	}

	/**
	 * Returns the array representation.
	 *
	 * @since 0.1.0
	 *
	 * @return mixed[] Array representation.
	 */
	public function to_array(): array {
		return array_map(
			static function ( Candidate $candidate ) {
				return $candidate->to_array();
			},
			$this->candidates
		);
	}

	/**
	 * Creates a Candidates instance from an array of candidates data.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed>[] $data The candidates data.
	 * @return Candidates The Candidates instance.
	 *
	 * @throws InvalidArgumentException Thrown if the candidates data is invalid.
	 */
	public static function from_array( array $data ): Candidates {
		$candidates = new Candidates();

		foreach ( $data as $candidate ) {
			if ( ! is_array( $candidate ) ) {
				throw new InvalidArgumentException( 'Invalid candidate data.' );
			}

			$candidates->add_candidate( Candidate::from_array( $candidate ) );
		}

		return $candidates;
	}
}
