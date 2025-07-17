<?php
/**
 * Trait Felix_Arntz\AI_Services\Mock\Traits\With_Mock_Results_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Mock\Traits;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Util\Formatter;

/**
 * Trait for a mock model which implements the With_Mock_Results interface.
 *
 * @since n.e.x.t
 */
trait With_Mock_Results_Trait {

	/**
	 * The expected candidates object.
	 *
	 * @since n.e.x.t
	 * @var ?Candidates
	 */
	private $expected_candidates = null;

	/**
	 * The callbacks for expected candidates objects.
	 *
	 * @since n.e.x.t
	 * @var callable[]
	 */
	private $expected_candidates_callbacks = array();

	/**
	 * Sets the mock content to expect from subsequent AI requests, or a request satisfying certain criteria.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Content|Candidates|callable $content The mock content to expect for any subsequent AI requests. A
	 *                                                    callback can be provided alternatively to a concrete result,
	 *                                                    which will receive the given input contents array, and must
	 *                                                    return either one of the concrete results supported, or null
	 *                                                    in case it should not apply.
	 *                                                    Multiple callbacks can be stored. If both callbacks and a concrete
	 *                                                    value are present, the callbacks will take precedence, with the
	 *                                                    concrete value used as fallback.
	 */
	final public function expect_content( $content ): void {
		if ( is_callable( $content ) ) {
			// Wrap the passed callback to ensure consistent return values.
			$this->expected_candidates_callbacks[] = function ( array $contents ) use ( $content ) {
				$candidates = $content( $contents );
				if ( ! $candidates ) {
					return null;
				}
				return $this->parse_candidates( $candidates );
			};
			return;
		}

		$this->expected_candidates = $this->parse_candidates( $content );
	}

	/**
	 * Resolves the expected candidates for the given contents.
	 *
	 * If no expected candidates were provided and no provided expected callbacks match, a fallback default response
	 * will be returned.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $contents Contents (commonly containing only a single item) with the AI request.
	 * @return Candidates The resolved candidates.
	 */
	final protected function resolve_expected_candidates( array $contents ): Candidates {
		foreach ( $this->expected_candidates_callbacks as $callback ) {
			$candidates = $callback( $contents );
			if ( null !== $candidates ) {
				return $candidates;
			}
		}

		if ( null !== $this->expected_candidates ) {
			return $this->expected_candidates;
		}

		return $this->get_default_candidates();
	}

	/**
	 * Gets the default candidates to return for AI requests where no expected response was provided.
	 *
	 * @since n.e.x.t
	 *
	 * @return Candidates The default candidates.
	 */
	abstract protected function get_default_candidates(): Candidates;

	/**
	 * Parses the given content into a Candidates object.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content|Candidate|Candidates $content The content to parse.
	 * @return Candidates The resulting Candidates object.
	 */
	final protected function parse_candidates( $content ): Candidates {
		if ( $content instanceof Candidates ) {
			return $content;
		}

		if ( ! $content instanceof Candidate ) {
			$content = Formatter::format_content( $content, Content_Role::MODEL );
			$content = new Candidate( $content );
		}

		$candidates = new Candidates();
		$candidates->add_candidate( $content );
		return $candidates;
	}
}
