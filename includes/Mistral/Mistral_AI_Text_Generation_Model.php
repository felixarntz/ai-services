<?php
/**
 * Class Felix_Arntz\AI_Services\Mistral\Mistral_AI_Text_Generation_Model
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Mistral;

use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\Base\OpenAI_Compatible_AI_Text_Generation_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_Function_Calling;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Traits\OpenAI_Compatible_Text_Generation_With_Function_Calling_Trait;
use InvalidArgumentException;

/**
 * Class representing a Mistral text generation AI model.
 *
 * @since 0.7.0
 */
class Mistral_AI_Text_Generation_Model extends OpenAI_Compatible_AI_Text_Generation_Model implements With_Function_Calling, With_Multimodal_Input {
	use OpenAI_Compatible_Text_Generation_With_Function_Calling_Trait;

	/**
	 * Constructor.
	 *
	 * @since 0.7.0
	 *
	 * @param Generative_AI_API_Client $api_client      The AI API client instance.
	 * @param Model_Metadata           $metadata        The model metadata.
	 * @param array<string, mixed>     $model_params    Optional. Additional model parameters. See
	 *                                                  {@see Mistral_AI_Service::get_model()} for the list of available
	 *                                                  parameters. Default empty array.
	 * @param array<string, mixed>     $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Generative_AI_API_Client $api_client, Model_Metadata $metadata, array $model_params = array(), array $request_options = array() ) {
		parent::__construct( $api_client, $metadata, $model_params, $request_options );

		$this->set_tool_config_from_model_params( $model_params );
		$this->set_tools_from_model_params( $model_params );
	}
}
