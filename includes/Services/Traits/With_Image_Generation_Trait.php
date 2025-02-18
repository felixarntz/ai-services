<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\With_Image_Generation_Trait
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use InvalidArgumentException;

/**
 * Trait for a model which implements the With_Image_Generation interface.
 *
 * @since n.e.x.t
 */
trait With_Image_Generation_Trait {

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
	 * @throws InvalidArgumentException Thrown if the given content is invalid.
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	final public function generate_image( $content, array $request_options = array() ): Candidates {
		$contents = $this->sanitize_new_content( $content );
		return $this->send_generate_image_request( $contents, $request_options );
	}

	/**
	 * Sanitizes the input content for generating an image.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content|Content[] $content The input content.
	 * @return Content[] The sanitized content.
	 *
	 * @throws InvalidArgumentException Thrown if the input content is invalid.
	 */
	private function sanitize_new_content( $content ) {
		$capabilities = AI_Capabilities::get_model_instance_capabilities( $this );
		return Formatter::format_and_validate_new_contents( $content, $capabilities );
	}

	/**
	 * Sends a request to generate an image.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	abstract protected function send_generate_image_request( array $contents, array $request_options ): Candidates;
}
