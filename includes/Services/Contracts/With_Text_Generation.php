<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Types\Candidates;
use Felix_Arntz\AI_Services\Services\Types\Chat_Session;
use Felix_Arntz\AI_Services\Services\Types\Content;
use Felix_Arntz\AI_Services\Services\Types\Parts;
use InvalidArgumentException;

/**
 * Interface for a model which allows generating text content.
 *
 * @since n.e.x.t
 */
interface With_Text_Generation {

	/**
	 * Generates text content using the model.
	 *
	 * @since n.e.x.t
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
	 * Starts a multi-turn chat session using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $history Optional. The chat history. Default empty array.
	 * @return Chat_Session The chat session.
	 */
	public function start_chat( array $history = array() ): Chat_Session;
}
