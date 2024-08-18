<?php
/**
 * Interface Vendor_NS\WP_Starter_Plugin\Services\Contracts\With_Image_Generation
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Contracts;

use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Candidate;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Content;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Parts;

/**
 * Interface for a class (typically a model) which allows generating images.
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
	 * @return Candidate[] The response candidates with generated content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function generate_image( $content, array $request_options = array() ): array;
}
