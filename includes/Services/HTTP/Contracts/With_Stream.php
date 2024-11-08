<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\HTTP\Contracts\With_Stream
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\HTTP\Contracts;

use Generator;

/**
 * Interface for a class that contains a readable stream.
 *
 * @since n.e.x.t
 */
interface With_Stream {

	/**
	 * Returns a generator that reads individual chunks of decoded JSON data from the streamed response body.
	 *
	 * @since n.e.x.t
	 *
	 * @return Generator The generator for the response stream.
	 */
	public function read_stream(): Generator;
}
