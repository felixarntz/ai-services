<?php
/**
 * Trait Felix_Arntz\AI_Services\Services\Traits\With_Text_To_Speech_Trait
 *
 * @since 0.7.0
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
 * Trait for a model which implements the With_Text_To_Speech interface.
 *
 * @since 0.7.0
 */
trait With_Text_To_Speech_Trait {

	/**
	 * Transforms text to speech using the model.
	 *
	 * @since 0.7.0
	 *
	 * @param string|Parts|Content|Content[] $content         The content to transform to speech.
	 * @param array<string, mixed>           $request_options Optional. The request options. Default empty array.
	 * @return Candidates The response candidates with generated speech - usually just one.
	 *
	 * @throws InvalidArgumentException Thrown if the given content is invalid.
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	final public function text_to_speech( $content, array $request_options = array() ): Candidates {
		$contents = $this->sanitize_new_content( $content );
		return $this->send_text_to_speech_request( $contents, $request_options );
	}

	/**
	 * Sanitizes the input content for text to speech.
	 *
	 * @since 0.7.0
	 *
	 * @param string|Parts|Content|Content[] $content The input content.
	 * @return Content[] The sanitized content.
	 *
	 * @throws InvalidArgumentException Thrown if the input content is invalid.
	 */
	private function sanitize_new_content( $content ): array {
		$capabilities = AI_Capabilities::get_model_instance_capabilities( $this );
		return Formatter::format_and_validate_new_contents( $content, $capabilities );
	}

	/**
	 * Sends a request to transform text to speech.
	 *
	 * @since 0.7.0
	 *
	 * @param Content[]            $contents        The content to transform to speech.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated speech - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	abstract protected function send_text_to_speech_request( array $contents, array $request_options ): Candidates;
}
