<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\Service_Generate_Content_REST_Route
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Google\Types\Safety_Setting;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Services_API;
use Felix_Arntz\AI_Services\Services\Types\Content;
use Felix_Arntz\AI_Services\Services\Types\Generation_Config;
use Felix_Arntz\AI_Services\Services\Types\Parts;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Route;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Exception\REST_Exception;
use InvalidArgumentException;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class representing the REST API route for getting a service.
 *
 * @since 0.1.0
 */
class Service_Generate_Content_REST_Route extends Abstract_REST_Route {
	const BASE    = '/services/(?P<slug>[\w-]+):generate-text';
	const METHODS = WP_REST_Server::CREATABLE;

	/**
	 * The services API instance.
	 *
	 * @since 0.1.0
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * Current user service.
	 *
	 * @since 0.1.0
	 * @var Current_User
	 */
	private $current_user;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Services_API $services_api    The services API instance.
	 * @param Current_User $current_user    The current user service.
	 */
	public function __construct( Services_API $services_api, Current_User $current_user ) {
		$this->services_api = $services_api;
		$this->current_user = $current_user;

		parent::__construct();
	}

	/**
	 * Returns the route base.
	 *
	 * @since 0.1.0
	 *
	 * @return string Route base.
	 */
	protected function base(): string {
		return self::BASE;
	}

	/**
	 * Returns the route methods, as a comma-separated string.
	 *
	 * @since 0.1.0
	 *
	 * @return string Route methods, as a comma-separated string.
	 */
	protected function methods(): string {
		return self::METHODS;
	}

	/**
	 * Checks the required permissions for the given request and throws an exception if they aren't met.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request WordPress REST request object, including parameters.
	 *
	 * @throws REST_Exception Thrown when the permissions aren't met, or when a REST error occurs.
	 */
	protected function check_permissions( WP_REST_Request $request ): void /* @phpstan-ignore-line */ {
		if ( ! $this->current_user->has_cap( 'ais_access_service', $request['slug'] ) ) {
			throw REST_Exception::create(
				'rest_cannot_view',
				esc_html__( 'Sorry, you are not allowed to access this service.', 'ai-services' ),
				$this->current_user->is_logged_in() ? 403 : 401
			);
		}
	}

	/**
	 * Handles the given request and returns a response.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request WordPress REST request object, including parameters.
	 * @return WP_REST_Response WordPress REST response object.
	 *
	 * @throws REST_Exception Thrown when a REST error occurs.
	 */
	protected function handle_request( WP_REST_Request $request ): WP_REST_Response /* @phpstan-ignore-line */ {
		if ( ! $this->services_api->is_service_registered( $request['slug'] ) ) {
			throw REST_Exception::create(
				'rest_service_invalid_slug',
				esc_html__( 'Invalid service slug.', 'ai-services' ),
				404
			);
		}

		if ( ! $this->services_api->is_service_available( $request['slug'] ) ) {
			throw REST_Exception::create(
				'rest_service_not_available',
				esc_html__( 'The service is not available.', 'ai-services' ),
				400
			);
		}

		$service      = $this->services_api->get_available_service( $request['slug'] );
		$model_params = $this->process_model_params( $request['modelParams'] ?? array() );
		$model        = $this->get_model( $service, $model_params );

		// Parse content data into one of the supported formats.
		$content = $this->parse_content( $request['content'] );

		try {
			$candidates = $model->generate_text( $content );
		} catch ( Generative_AI_Exception $e ) {
			throw REST_Exception::create(
				'rest_generating_content_failed',
				sprintf(
					/* translators: 1: model slug, 2: original error message */
					esc_html__( 'Generating content with model %1$s failed: %2$s', 'ai-services' ),
					esc_html( $model->get_model_slug() ),
					esc_html( $e->getMessage() )
				),
				500
			);
		} catch ( InvalidArgumentException $e ) {
			throw REST_Exception::create(
				'rest_invalid_content',
				sprintf(
					/* translators: 1: model slug, 2: original error message */
					esc_html__( 'Invalid content provided to model %1$s: %2$s', 'ai-services' ),
					esc_html( $model->get_model_slug() ),
					esc_html( $e->getMessage() )
				),
				400
			);
		}

		return rest_ensure_response( $candidates->to_array() );
	}

	/**
	 * Retrieves the (text-based) model with the given slug and parameters.
	 *
	 * @since 0.1.0
	 *
	 * @param Generative_AI_Service $service      The service instance to get the model from.
	 * @param array<string, mixed>  $model_params The model parameters.
	 * @return Generative_AI_Model&With_Text_Generation The model.
	 *
	 * @throws REST_Exception Thrown when the model cannot be retrieved or invalid parameters are provided.
	 */
	protected function get_model( Generative_AI_Service $service, array $model_params ): Generative_AI_Model {
		try {
			$model = $service->get_model( $model_params );
		} catch ( Generative_AI_Exception $e ) {
			throw REST_Exception::create(
				'rest_cannot_get_model',
				sprintf(
					/* translators: %s: original error message */
					esc_html__( 'Getting the model failed: %s', 'ai-services' ),
					esc_html( $e->getMessage() )
				),
				500
			);
		} catch ( InvalidArgumentException $e ) {
			throw REST_Exception::create(
				'rest_invalid_model_params',
				sprintf(
					/* translators: %s: original error message */
					esc_html__( 'Invalid model slug or model params: %s', 'ai-services' ),
					esc_html( $e->getMessage() )
				),
				400
			);
		}

		if ( ! $model instanceof With_Text_Generation ) {
			throw REST_Exception::create(
				'rest_model_lacks_support',
				esc_html__( 'The model does not support text generation.', 'ai-services' ),
				400
			);
		}

		return $model;
	}

	/**
	 * Processes the model parameters before retrieving the model with them.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 * @return array<string, mixed> The processed model parameters.
	 */
	protected function process_model_params( array $model_params ): array {
		/*
		 * At the moment, only text generation is supported.
		 * As such, at the very least, text generation capabilities need to be supported by the model.
		 */
		if ( ! isset( $model_params['capabilities'] ) ) {
			$model_params['capabilities'] = array( AI_Capabilities::CAPABILITY_TEXT_GENERATION );
		}

		// Parse associative arrays into their relevant data structures.
		if ( isset( $model_params['generationConfig'] ) && is_array( $model_params['generationConfig'] ) ) {
			$model_params['generationConfig'] = Generation_Config::from_array( $model_params['generationConfig'] );
		}
		if ( isset( $model_params['systemInstruction'] ) && is_array( $model_params['systemInstruction'] ) ) {
			if ( isset( $model_params['systemInstruction']['role'] ) ) {
				$model_params['systemInstruction'] = Content::from_array( $model_params['systemInstruction'] );
			} else {
				$model_params['systemInstruction'] = Parts::from_array( $model_params['systemInstruction'] );
			}
		}

		// This is Google specific. TODO: Handle this via filter callback or similar.
		if ( isset( $model_params['safetySettings'] ) && is_array( $model_params['safetySettings'] ) ) {
			foreach ( $model_params['safetySettings'] as $index => $safety_setting ) {
				if ( is_array( $safety_setting ) ) {
					$model_params['safetySettings'][ $index ] = Safety_Setting::from_array( $safety_setting );
				}
			}
		}

		/**
		 * Filters the model parameters passed to the REST API before retrieving the model with them.
		 *
		 * This can be used, for example, to inject additional parameters via server-side logic in order to decouple
		 * them from the client-side implementation.
		 *
		 * @since 0.1.0
		 *
		 * @param array<string, mixed> $model_params The model parameters. Commonly supports at least the parameters
		 *                                           'feature', 'capabilities', 'generationConfig' and
		 *                                           'systemInstruction'.
		 * @return array<string, mixed> The processed model parameters.
		 */
		return (array) apply_filters( 'ai_services_rest_model_params', $model_params );
	}

	/**
	 * Returns the route specific arguments.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> Route arguments.
	 */
	protected function args(): array {
		$content_schema = array_merge(
			array( 'description' => __( 'Prompt content object.', 'ai-services' ) ),
			Content::get_json_schema()
		);

		$system_content_schema                                = $content_schema;
		$system_content_schema['properties']['role']['enum']  = array( Content::ROLE_SYSTEM );
		$user_content_schema                                  = $content_schema;
		$user_content_schema['properties']['role']['enum']    = array( Content::ROLE_USER );
		$history_content_schema                               = $content_schema;
		$history_content_schema['properties']['role']['enum'] = array( Content::ROLE_USER, Content::ROLE_MODEL );

		return array(
			'modelParams' => array(
				'description'          => __( 'Model parameters.', 'ai-services' ),
				'type'                 => 'object',
				'properties'           => array(
					'model'             => array(
						'description' => __( 'Model slug.', 'ai-services' ),
						'type'        => 'string',
					),
					'capabilities'      => array(
						'description' => __( 'Capabilities requested for the model to support.', 'ai-services' ),
						'type'        => 'array',
						'items'       => array(
							'type' => 'string',
						),
					),
					'generationConfig'  => array_merge(
						array( 'description' => __( 'Model generation configuration options.', 'ai-services' ) ),
						Generation_Config::get_json_schema()
					),
					'systemInstruction' => array(
						'description' => __( 'System instruction for the model.', 'ai-services' ),
						'type'        => array( 'string', 'object', 'array' ),
						'oneOf'       => array(
							array(
								'description' => __( 'Prompt text as a string.', 'ai-services' ),
								'type'        => 'string',
							),
							$system_content_schema,
						),
					),
				),
				'additionalProperties' => true,
			),
			'content'     => array(
				'description' => __( 'Content data to pass to the model, including the prompt and optional history.', 'ai-services' ),
				'type'        => array( 'string', 'object', 'array' ),
				'oneOf'       => array(
					array(
						'description' => __( 'Prompt text as a string.', 'ai-services' ),
						'type'        => 'string',
					),
					array_merge(
						array( 'description' => __( 'Prompt including multi modal data such as files.', 'ai-services' ) ),
						Parts::get_json_schema()
					),
					$user_content_schema,
					array(
						'description' => __( 'Array of contents, including history from previous user prompts and their model answers.', 'ai-services' ),
						'type'        => 'array',
						'minItems'    => 1,
						'items'       => $history_content_schema,
					),
				),
			),
		);
	}

	/**
	 * Returns the global route arguments.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> Global route arguments.
	 */
	protected function global_args(): array {
		return array(
			'args'   => array(
				'slug' => array(
					'description' => __( 'Unique service slug.', 'ai-services' ),
					'type'        => 'string',
				),
			),
			'schema' => $this->get_schema(),
		);
	}

	/**
	 * Returns the response schema for the route.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> The schema for the route.
	 */
	private function get_schema(): array {
		return array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'candidate',
			'type'                 => 'object',
			'properties'           => array(
				'content' => array_merge(
					array(
						'description' => __( 'Candidate content.', 'ai-services' ),
						'readonly'    => true,
					),
					Content::get_json_schema()
				),
			),
			'additionalProperties' => true,
		);
	}

	/**
	 * Parses the content data into one of the formats expected by the {@see With_Text_Generation::generate_text()} method.
	 *
	 * The implementation of this method goes hand in hand with the schema definitions for the 'content' parameter.
	 *
	 * @since 0.1.0
	 * @see With_Text_Generation::generate_text()
	 *
	 * @param mixed $content The content data.
	 * @return string|Parts|Content|Content[] The parsed content data.
	 */
	private function parse_content( $content ) {
		if ( is_string( $content ) ) { // A simple text prompt.
			return $content;
		}

		if ( wp_is_numeric_array( $content ) ) {
			if ( isset( $content[0]['role'] ) ) { // An array of content, i.e. likely including history.
				return array_map(
					static function ( $content ) {
						return Content::from_array( $content );
					},
					$content
				);
			}

			return Parts::from_array( $content ); // An array of parts, i.e. likely a multi-modal prompt.
		}

		return Content::from_array( $content );
	}
}
