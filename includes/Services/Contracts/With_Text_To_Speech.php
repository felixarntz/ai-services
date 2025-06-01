<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\With_Text_To_Speech
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use InvalidArgumentException;

/**
 * Interface for a model which allows transforming text to speech.
 *
 * @since n.e.x.t
 */
interface With_Text_To_Speech {

	/**
	 * Transforms text to speech using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content|Content[] $content         The content to transform to speech.
	 * @param array<string, mixed>           $request_options Optional. The request options. Default empty array.
	 * @return Candidates The response candidates with generated speech - usually just one.
	 *
	 * @throws InvalidArgumentException Thrown if the given content is invalid.
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function text_to_speech( $content, array $request_options = array() ): Candidates;
}
