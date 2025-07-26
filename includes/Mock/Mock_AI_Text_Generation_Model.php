<?php
/**
 * Class Felix_Arntz\AI_Services\Mock\Mock_AI_Text_Generation_Model
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Mock;

use Felix_Arntz\AI_Services\Mock\Contracts\With_Mock_Results;
use Felix_Arntz\AI_Services\Mock\Traits\With_Mock_Results_Trait;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_Chat_History;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_System_Instruction_Trait;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_Text_Generation_Config_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Chat_History_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait;
use Generator;
use InvalidArgumentException;

/**
 * Class representing a mock text generation AI model.
 *
 * @since n.e.x.t
 */
class Mock_AI_Text_Generation_Model extends Abstract_AI_Model implements With_Mock_Results, With_Text_Generation, With_Chat_History, With_Multimodal_Input {
	use With_Mock_Results_Trait;
	use With_Text_Generation_Trait;
	use With_Chat_History_Trait;
	use Model_Param_Text_Generation_Config_Trait;
	use Model_Param_System_Instruction_Trait;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Model_Metadata       $metadata        The model metadata.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. See
	 *                                              {@see Mock_AI_Service::get_model()} for the list of available
	 *                                              parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Model_Metadata $metadata, array $model_params = array(), array $request_options = array() ) {
		$this->set_model_metadata( $metadata );

		$this->set_text_generation_config_from_model_params( $model_params );
		$this->set_system_instruction_from_model_params( $model_params );

		$this->set_request_options( $request_options );
	}

	/**
	 * Sends a request to generate text content.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_generate_text_request( array $contents, array $request_options ): Candidates {
		return $this->resolve_expected_candidates( $contents );
	}

	/**
	 * Sends a request to generate text content, streaming the response.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Generator<Candidates> Generator that yields the chunks of response candidates with generated text
	 *                               content - usually just one candidate.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_stream_generate_text_request( array $contents, array $request_options ): Generator {
		$candidates = $this->resolve_expected_candidates( $contents );
		yield $candidates;
	}

	/**
	 * Gets the default candidates to return for AI requests where no expected response was provided.
	 *
	 * @since n.e.x.t
	 *
	 * @return Candidates The default candidates.
	 */
	protected function get_default_candidates(): Candidates {
		return $this->parse_candidates(
			'This is the default AI response to a mock request for which no expected response was provided.'
		);
	}
}
