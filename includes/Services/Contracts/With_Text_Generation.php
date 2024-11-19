<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Generator;
use InvalidArgumentException;

/**
 * Interface for a model which allows generating text content.
 *
 * @since 0.1.0
 */
interface With_Text_Generation {

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
	public function generate_text( $content, array $request_options = array() ): Candidates;

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
	public function stream_generate_text( $content, array $request_options = array() ): Generator;
}
