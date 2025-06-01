<?php
/**
 * Class Felix_Arntz\AI_Services\Perplexity\Perplexity_AI_Text_Generation_Model
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Perplexity;

use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools\Web_Search_Tool;
use Felix_Arntz\AI_Services\Services\Base\OpenAI_Compatible_AI_Text_Generation_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Contracts\With_Web_Search;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_Tools_Trait;
use InvalidArgumentException;

/**
 * Class representing a Perplexity text generation AI model.
 *
 * @since n.e.x.t
 */
class Perplexity_AI_Text_Generation_Model extends OpenAI_Compatible_AI_Text_Generation_Model implements With_Multimodal_Input, With_Web_Search {
	use Model_Param_Tools_Trait;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Generative_AI_API_Client $api_client      The AI API client instance.
	 * @param Model_Metadata           $metadata        The model metadata.
	 * @param array<string, mixed>     $model_params    Optional. Additional model parameters. See
	 *                                                  {@see Perplexity_AI_Service::get_model()} for the list of available
	 *                                                  parameters. Default empty array.
	 * @param array<string, mixed>     $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Generative_AI_API_Client $api_client, Model_Metadata $metadata, array $model_params = array(), array $request_options = array() ) {
		parent::__construct( $api_client, $metadata, $model_params, $request_options );

		$this->set_tools_from_model_params( $model_params );
	}

	/**
	 * Prepares the API request parameters for generating text content.
	 *
	 * @since 0.3.0
	 *
	 * @param Content[] $contents The contents to generate text for.
	 * @return array<string, mixed> The parameters for generating text content.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid tool is provided.
	 */
	protected function prepare_generate_text_params( array $contents ): array {
		$params = parent::prepare_generate_text_params( $contents );

		if ( $this->get_tools() ) {
			/*
			* Add 'search_domain_filter' parameter if needed based on any web search tools provided.
			* Web search itself is ALWAYS enabled in Perplexity, and it cannot be turned off.
			* So there is no need to opt in or out of it.
			*/
			$domain_filters = array();
			foreach ( $this->get_tools() as $tool ) {
				if ( ! $tool instanceof Web_Search_Tool ) {
					throw $this->get_api_client()->create_bad_request_exception(
						'Only web search tools are supported.'
					);
				}

				$allowed_domains    = $tool->get_allowed_domains();
				$disallowed_domains = $tool->get_disallowed_domains();

				foreach ( $allowed_domains as $domain ) {
					$domain_filters[] = $domain;
				}
				foreach ( $disallowed_domains as $domain ) {
					$domain_filters[] = '-' . $domain;
				}
			}
			if ( count( $domain_filters ) > 0 ) {
				$params['search_domain_filter'] = implode( ',', $domain_filters );
			}
		}

		return $params;
	}

	/**
	 * Gets the generation configuration transformers.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, callable> The generation configuration transformers.
	 */
	protected function get_generation_config_transformers(): array {
		$transformers = parent::get_generation_config_transformers();

		// Perplexity calls this 'max_tokens' instead of 'max_completion_tokens'.
		$transformers['max_tokens'] = $transformers['max_completion_tokens'];
		unset( $transformers['max_completion_tokens'] );

		// Perplexity does not support the following parameters, so we remove them.
		unset(
			$transformers['stop'],
			$transformers['n'],
			$transformers['logprobs'],
			$transformers['top_logprobs']
		);

		// Perplexity supports 'top_k', which is not a standard OpenAI parameter.
		$transformers['top_k'] = static function ( Text_Generation_Config $config ) {
			return $config->get_top_k();
		};

		return $transformers;
	}
}
