<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Traits;

use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Generator;
use InvalidArgumentException;

/**
 * Trait for a model which implements the With_Text_Generation interface.
 *
 * @since 0.1.0
 */
trait With_Text_Generation_Trait {

	/**
	 * Generates text content using the model.
	 *
	 * @since 0.1.0
	 *
	 * @param string|Parts|Content|Content[] $content         Prompt for the content to generate. Optionally, an array
	 *                                                        can be passed for additional context (e.g. chat history).
	 * @param array<string, mixed>           $request_options Optional. The request options. Default empty array.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws InvalidArgumentException Thrown if the given content is invalid.
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	final public function generate_text( $content, array $request_options = array() ): Candidates {
		$contents = $this->sanitize_new_content( $content );
		return $this->send_generate_text_request( $contents, $request_options );
	}

	/**
	 * Generates text content using the model, streaming the response.
	 *
	 * @since 0.3.0
	 *
	 * @param string|Parts|Content|Content[] $content         Prompt for the content to generate. Optionally, an array
	 *                                                        can be passed for additional context (e.g. chat history).
	 * @param array<string, mixed>           $request_options Optional. The request options. Default empty array.
	 * @return Generator<Candidates> Generator that yields the chunks of response candidates with generated text
	 *                               content - usually just one candidate.
	 *
	 * @throws InvalidArgumentException Thrown if the given content is invalid.
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	final public function stream_generate_text( $content, array $request_options = array() ): Generator {
		$contents = $this->sanitize_new_content( $content );
		return $this->send_stream_generate_text_request( $contents, $request_options );
	}

	/**
	 * Sanitizes the input content for generating text.
	 *
	 * @since 0.3.0
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
	 * Sends a request to generate text content.
	 *
	 * @since 0.1.0
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	abstract protected function send_generate_text_request( array $contents, array $request_options ): Candidates;

	/**
	 * Sends a request to generate text content, streaming the response.
	 *
	 * @since 0.3.0
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Generator<Candidates> Generator that yields the chunks of response candidates with generated text
	 *                               content - usually just one candidate.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	abstract protected function send_stream_generate_text_request( array $contents, array $request_options ): Generator;
}
