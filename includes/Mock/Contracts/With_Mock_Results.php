<?php
/**
 * Interface Felix_Arntz\AI_Services\Mock\Contracts\With_Mock_Results
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Mock\Contracts;

use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;

/**
 * Interface for a mock model class that can receive expected results.
 *
 * @since 0.7.0
 */
interface With_Mock_Results {

	/**
	 * Sets the mock content to expect from subsequent AI requests, or a request satisfying certain criteria.
	 *
	 * @since 0.7.0
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
	public function expect_content( $content ): void;
}
