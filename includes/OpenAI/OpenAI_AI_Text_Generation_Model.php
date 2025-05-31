<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Text_Generation_Model
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Felix_Arntz\AI_Services\Services\API\Types\Contracts\Tool;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\Base\OpenAI_Compatible_AI_Text_Generation_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_Function_Calling;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Traits\OpenAI_Compatible_With_Function_Calling_Trait;
use InvalidArgumentException;

/**
 * Class representing an OpenAI text generation AI model.
 *
 * @since 0.1.0
 * @since 0.5.0 Renamed from `OpenAI_AI_Model`.
 * @since n.e.x.t Now extends `OpenAI_Compatible_AI_Text_Generation_Model` instead of `Abstract_AI_Model`.
 */
class OpenAI_AI_Text_Generation_Model extends OpenAI_Compatible_AI_Text_Generation_Model implements With_Function_Calling, With_Multimodal_Input {
	use OpenAI_Compatible_With_Function_Calling_Trait {
		prepare_tool as prepare_function_declarations_tool;
	}

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Generative_AI_API_Client $api_client      The AI API client instance.
	 * @param Model_Metadata           $metadata        The model metadata.
	 * @param array<string, mixed>     $model_params    Optional. Additional model parameters. See
	 *                                                  {@see OpenAI_AI_Service::get_model()} for the list of available
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

	/**
	 * Prepares a single tool for the API request, amending the provided parameters as needed.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $params The parameters to prepare the tools for. Passed by reference.
	 * @param Tool                 $tool   The tool to prepare.
	 * @return bool True if the tool was successfully prepared, false otherwise.
	 */
	protected function prepare_tool( array &$params, Tool $tool ): bool {
		$result = $this->prepare_function_declarations_tool( $params, $tool );
		if ( ! $result ) {
			return false;
		}

		/*
		 * The OpenAI API supports a 'strict' argument for function tools, which is not part of the standard OpenAI API
		 * specification and therefore may not be supported by other providers.
		 * Since it makes sense to always use it for OpenAI, we add it here if not set.
		 */
		if ( isset( $params['tools'] ) && is_array( $params['tools'] ) ) {
			$params['tools'] = array_map(
				function ( $openai_tool_data ) {
					if ( ! isset( $openai_tool_data['type'] ) || 'function' !== $openai_tool_data['type'] ) {
						return $openai_tool_data;
					}
					// Add the 'strict' argument to the function tool if not set.
					if ( ! isset( $openai_tool_data['function']['strict'] ) ) {
						$openai_tool_data['function']['strict'] = true;
					}
					return $openai_tool_data;
				},
				$params['tools']
			);
		}

		return true;
	}
}
