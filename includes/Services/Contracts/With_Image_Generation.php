<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\With_Image_Generation
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Types\Candidates;
use Felix_Arntz\AI_Services\Services\Types\Content;
use Felix_Arntz\AI_Services\Services\Types\Parts;

/**
 * Interface for a model which allows generating images.
 *
 * @since n.e.x.t
 */
interface With_Image_Generation {

	/**
	 * Generates an image using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content|Content[] $content         Prompt for the image to generate. Optionally, an array
	 *                                                        can be passed for additional context (e.g. chat history).
	 * @param array<string, mixed>           $request_options Optional. The request options. Default empty array.
	 * @return Candidates The response candidates with generated images - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function generate_image( $content, array $request_options = array() ): Candidates;
}
