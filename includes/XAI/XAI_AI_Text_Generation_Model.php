<?php
/**
 * Class Felix_Arntz\AI_Services\XAI\XAI_AI_Text_Generation_Model
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\XAI;

use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Contracts\Tool;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Tools\Web_Search_Tool;
use Felix_Arntz\AI_Services\Services\Base\OpenAI_Compatible_AI_Text_Generation_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_Function_Calling;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Contracts\With_Web_Search;
use Felix_Arntz\AI_Services\Services\Traits\OpenAI_Compatible_Text_Generation_With_Function_Calling_Trait;
use InvalidArgumentException;

/**
 * Class representing an xAI text generation AI model.
 *
 * @since 0.7.0
 */
class XAI_AI_Text_Generation_Model extends OpenAI_Compatible_AI_Text_Generation_Model implements With_Function_Calling, With_Multimodal_Input, With_Web_Search {
	use OpenAI_Compatible_Text_Generation_With_Function_Calling_Trait {
		prepare_generate_text_params as prepare_generate_text_params_with_function_calling;
		prepare_tool as prepare_function_declarations_tool;
	}

	/**
	 * Constructor.
	 *
	 * @since 0.7.0
	 *
	 * @param Generative_AI_API_Client $api_client      The AI API client instance.
	 * @param Model_Metadata           $metadata        The model metadata.
	 * @param array<string, mixed>     $model_params    Optional. Additional model parameters. See
	 *                                                  {@see XAI_AI_Service::get_model()} for the list of available
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
	 * Prepares the API request parameters for generating text content.
	 *
	 * @since 0.7.0
	 *
	 * @param Content[] $contents The contents to generate text for.
	 * @return array<string, mixed> The parameters for generating text content.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid tool is provided.
	 */
	protected function prepare_generate_text_params( array $contents ): array {
		$params = $this->prepare_generate_text_params_with_function_calling( $contents );

		/*
		 * xAI sets Live Search as 'auto' by default.
		 * But our API requires opt-in, so we set it to 'off' by default for consistency.
		 */
		if ( ! isset( $params['search_parameters'] ) ) {
			$params['search_parameters'] = array( 'mode' => 'off' );
		}

		return $params;
	}

	/**
	 * Prepares a single tool for the API request, amending the provided parameters as needed.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $params The parameters to prepare the tools for. Passed by reference.
	 * @param Tool                 $tool   The tool to prepare.
	 * @return bool True if the tool was successfully prepared, false otherwise.
	 */
	protected function prepare_tool( array &$params, Tool $tool ): bool {
		$prepared = $this->prepare_function_declarations_tool( $params, $tool );
		if ( $prepared ) {
			return true;
		}

		if ( ! $tool instanceof Web_Search_Tool ) {
			return false;
		}

		$disallowed_domains = $tool->get_disallowed_domains();

		$web_search_config = array(
			'type' => 'web',
		);
		if ( count( $disallowed_domains ) > 0 ) {
			$web_search_config['excluded_websites'] = $disallowed_domains;
		}

		$params['search_parameters'] = array(
			'mode'    => 'on',
			'sources' => array( $web_search_config ),
		);

		return true;
	}
}
