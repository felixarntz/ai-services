<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Service
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Tool_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;
use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;
use InvalidArgumentException;

/**
 * Class for the OpenAI AI service.
 *
 * @since 0.1.0
 */
class OpenAI_AI_Service implements Generative_AI_Service {

	/**
	 * The OpenAI AI API instance.
	 *
	 * @since 0.1.0
	 * @var OpenAI_AI_API_Client
	 */
	private $api;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Authentication $authentication The authentication credentials.
	 * @param HTTP           $http           Optional. The HTTP instance to use for requests. Default is a new instance.
	 */
	public function __construct( Authentication $authentication, HTTP $http = null ) {
		if ( ! $http ) {
			$http = new HTTP();
		}
		$this->api = new OpenAI_AI_API_Client( $authentication, $http );
	}

	/**
	 * Gets the service slug.
	 *
	 * @since 0.1.0
	 *
	 * @return string The service slug.
	 */
	public function get_service_slug(): string {
		return 'openai';
	}

	/**
	 * Gets the list of AI capabilities that the service and its models support.
	 *
	 * @since 0.1.0
	 * @see AI_Capabilities
	 *
	 * @return string[] The list of AI capabilities.
	 */
	public function get_capabilities(): array {
		return AI_Capabilities::get_model_class_capabilities( OpenAI_AI_Model::class );
	}

	/**
	 * Checks whether the service is connected.
	 *
	 * This is typically used to check whether the current service credentials are valid.
	 *
	 * @since 0.2.0
	 *
	 * @return bool True if the service is connected, false otherwise.
	 */
	public function is_connected(): bool {
		try {
			$this->list_models();
			return true;
		} catch ( Generative_AI_Exception $e ) {
			return false;
		}
	}

	/**
	 * Lists the available generative model slugs and their capabilities.
	 *
	 * @since 0.1.0
	 * @since n.e.x.t Return type changed to a map of model data shapes.
	 *
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return array<string, array{slug: string, name:string, capabilities: string[]}> Data for each model, mapped by
	 *                                                                                 model slug.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function list_models( array $request_options = array() ): array {
		$request       = $this->api->create_list_models_request( array(), $request_options );
		$response_data = $this->api->make_request( $request )->get_data();

		if ( ! isset( $response_data['data'] ) || ! $response_data['data'] ) {
			throw $this->api->create_missing_response_key_exception( 'data' );
		}

		// Unfortunately, the OpenAI API does not return model capabilities, so we have to hardcode them here.
		$gpt_capabilities            = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::TEXT_GENERATION,
		);
		$gpt_multimodal_capabilities = array(
			AI_Capability::CHAT_HISTORY,
			AI_Capability::MULTIMODAL_INPUT,
			AI_Capability::TEXT_GENERATION,
		);

		return array_reduce(
			$response_data['data'],
			static function ( array $models_data, array $model_data ) use ( $gpt_capabilities, $gpt_multimodal_capabilities ) {
				$model_slug = $model_data['id'];

				if (
					str_starts_with( $model_slug, 'gpt-' )
					&& ! str_contains( $model_slug, '-instruct' )
					&& ! str_contains( $model_slug, '-realtime' )
					&& ! str_contains( $model_slug, '-audio' )
				) {
					if ( str_starts_with( $model_slug, 'gpt-4o' ) ) {
						$model_caps = $gpt_multimodal_capabilities;
					} else {
						$model_caps = $gpt_capabilities;
					}
				} else {
					/*
					 * TODO: Support other models once capabilities are added.
					 * For example, dall-e models for image generation, tts models for text-to-speech.
					 */
					$model_caps = array();
				}

				$models_data[ $model_slug ] = array(
					'slug'         => $model_slug,
					'name'         => $model_slug, // The OpenAI API does not return a display name.
					'capabilities' => $model_caps,
				);
				return $models_data;
			},
			array()
		);
	}

	/**
	 * Gets a generative model instance for the provided model parameters.
	 *
	 * @since 0.1.0
	 * @since n.e.x.t Support for the $tools and $toolConfig arguments was added.
	 *
	 * @param array<string, mixed> $model_params    {
	 *     Optional. Model parameters. Default empty array.
	 *
	 *     @type string                 $feature           Required. Unique identifier of the feature that the model
	 *                                                     will be used for. Must only contain lowercase letters,
	 *                                                     numbers, hyphens.
	 *     @type string                 $model             The model slug. By default, the model will be determined
	 *                                                     based on heuristics such as the requested capabilities.
	 *     @type string[]               $capabilities      Capabilities requested for the model to support. It is
	 *                                                     recommended to specify this if you do not explicitly specify
	 *                                                     a model slug.
	 *     @type Tools|null             $tools             The tools to use for the model. Default none.
	 *     @type Tool_Config|null       $toolConfig        Tool configuration options. Default none.
	 *     @type Generation_Config|null $generationConfig  Model generation configuration options.  Default none.
	 *     @type string|Parts|Content   $systemInstruction The system instruction for the model. Default none.
	 * }
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Generative_AI_Model The generative model.
	 *
	 * @throws InvalidArgumentException Thrown if the model slug or parameters are invalid.
	 * @throws Generative_AI_Exception Thrown if getting the model fails.
	 */
	public function get_model( array $model_params = array(), array $request_options = array() ): Generative_AI_Model {
		if ( isset( $model_params['model'] ) ) {
			$model = $model_params['model'];
			unset( $model_params['model'] );
		} else {
			if ( isset( $model_params['capabilities'] ) ) {
				$model_slugs = AI_Capabilities::get_model_slugs_for_capabilities(
					$this->list_models( $request_options ),
					$model_params['capabilities']
				);
			} else {
				$model_slugs = array_keys( $this->list_models( $request_options ) );
			}
			$model = $this->sort_models_by_preference( $model_slugs )[0];
		}

		return new OpenAI_AI_Model( $this->api, $model, $model_params, $request_options );
	}

	/**
	 * Sorts model slugs by preference.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $model_slugs The model slugs to sort.
	 * @return string[] The model slugs, sorted by preference.
	 */
	private function sort_models_by_preference( array $model_slugs ): array {
		$get_preference_group = static function ( $model_slug ) {
			if ( str_starts_with( $model_slug, 'gpt-3.5' ) ) {
				if ( str_ends_with( $model_slug, '-turbo' ) ) {
					return 0;
				}
				if ( str_contains( $model_slug, '-turbo' ) ) {
					return 1;
				}
				return 2;
			}
			if ( str_starts_with( $model_slug, 'gpt-' ) ) {
				if ( str_contains( $model_slug, '-turbo' ) ) {
					return 3;
				}
				return 4;
			}
			return 5;
		};

		$preference_groups = array_fill( 0, 6, array() );
		foreach ( $model_slugs as $model_slug ) {
			$group                         = $get_preference_group( $model_slug );
			$preference_groups[ $group ][] = $model_slug;
		}

		return array_merge( ...$preference_groups );
	}
}
