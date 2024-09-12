<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\REST_Routes\Service_Generate_Content_REST_Route
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\REST_Routes;

use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Model;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\With_Text_Generation;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Services_API;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Content;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Parts;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Route;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Exception\REST_Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class representing the REST API route for getting a service.
 *
 * @since n.e.x.t
 */
class Service_Generate_Content_REST_Route extends Abstract_REST_Route {
	const BASE    = '/services/(?P<slug>[\w-]+):generate-text';
	const METHODS = WP_REST_Server::CREATABLE;

	/**
	 * The services API instance.
	 *
	 * @since n.e.x.t
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * Current user service.
	 *
	 * @since n.e.x.t
	 * @var Current_User
	 */
	private $current_user;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
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
	 * @since n.e.x.t
	 *
	 * @return string Route base.
	 */
	protected function base(): string {
		return self::BASE;
	}

	/**
	 * Returns the route methods, as a comma-separated string.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Route methods, as a comma-separated string.
	 */
	protected function methods(): string {
		return self::METHODS;
	}

	/**
	 * Checks the required permissions for the given request and throws an exception if they aren't met.
	 *
	 * @since n.e.x.t
	 *
	 * @param WP_REST_Request $request WordPress REST request object, including parameters.
	 *
	 * @throws REST_Exception Thrown when the permissions aren't met, or when a REST error occurs.
	 */
	protected function check_permissions( WP_REST_Request $request ): void /* @phpstan-ignore-line */ {
		if ( ! $this->current_user->has_cap( 'wpsp_access_service', $request['slug'] ) ) {
			throw REST_Exception::create(
				'rest_cannot_view',
				esc_html__( 'Sorry, you are not allowed to access this service.', 'wp-starter-plugin' ),
				$this->current_user->is_logged_in() ? 403 : 401
			);
		}
	}

	/**
	 * Handles the given request and returns a response.
	 *
	 * @since n.e.x.t
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
				esc_html__( 'Invalid service slug.', 'wp-starter-plugin' ),
				404
			);
		}

		if ( ! $this->services_api->is_service_available( $request['slug'] ) ) {
			throw REST_Exception::create(
				'rest_service_not_available',
				esc_html__( 'The service is not available.', 'wp-starter-plugin' ),
				400
			);
		}

		$service      = $this->services_api->get_available_service( $request['slug'] );
		$model_params = $this->process_model_params( $request['model_params'] ?? array() );
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
					esc_html__( 'Generating content with model %1$s failed: %2$s', 'wp-starter-plugin' ),
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
					esc_html__( 'Invalid content provided to model %1$s: %2$s', 'wp-starter-plugin' ),
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
	 * @since n.e.x.t
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
					esc_html__( 'Getting the model failed: %s', 'wp-starter-plugin' ),
					esc_html( $e->getMessage() )
				),
				500
			);
		} catch ( InvalidArgumentException $e ) {
			throw REST_Exception::create(
				'rest_invalid_model_params',
				sprintf(
					/* translators: %s: original error message */
					esc_html__( 'Invalid model slug or model params: %s', 'wp-starter-plugin' ),
					esc_html( $e->getMessage() )
				),
				400
			);
		}

		if ( ! $model instanceof With_Text_Generation ) {
			throw REST_Exception::create(
				'rest_model_lacks_support',
				esc_html__( 'The model does not support text generation.', 'wp-starter-plugin' ),
				400
			);
		}

		return $model;
	}

	/**
	 * Processes the model parameters before retrieving the model with them.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 * @return array<string, mixed> The processed model parameters.
	 */
	protected function process_model_params( array $model_params ): array {
		// Transforms common model parameters from camelCase to snake_case.
		$snake_case_map = array(
			'generationConfig'  => 'generation_config',
			'systemInstruction' => 'system_instruction',
		);
		foreach ( $snake_case_map as $camel_case => $snake_case ) {
			if ( isset( $model_params[ $camel_case ] ) ) {
				if ( ! isset( $model_params[ $snake_case ] ) ) {
					$model_params[ $snake_case ] = $model_params[ $camel_case ];
				}
				unset( $model_params[ $camel_case ] );
			}
		}

		/**
		 * Filters the model parameters passed to the REST API before retrieving the model with them.
		 *
		 * This can be used, for example, to inject additional parameters via server-side logic in order to decouple
		 * them from the client-side implementation.
		 *
		 * @since n.e.x.t
		 *
		 * @param array<string, mixed> $model_params The model parameters. Commonly supports at least the parameters
		 *                                           'generation_config' and 'system_instruction'.
		 * @return array<string, mixed> The processed model parameters.
		 */
		return (array) apply_filters( 'wp_starter_plugin_rest_model_params', $model_params );
	}

	/**
	 * Returns the route specific arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Route arguments.
	 */
	protected function args(): array {
		return array(
			'model_params' => array(
				'description'          => __( 'Model parameters.', 'wp-starter-plugin' ),
				'type'                 => 'object',
				'properties'           => array(
					'model'              => array(
						'description' => __( 'Model slug.', 'wp-starter-plugin' ),
						'type'        => 'string',
					),
					'generation_config'  => array(
						'description'          => __( 'Model generation configuration options.', 'wp-starter-plugin' ),
						'type'                 => 'object',
						'additionalProperties' => true,
					),
					'system_instruction' => array(
						'description' => __( 'System instruction for the model.', 'wp-starter-plugin' ),
						'type'        => array( 'string', 'object', 'array' ),
						'oneOf'       => array(
							array(
								'description' => __( 'Prompt text as a string.', 'wp-starter-plugin' ),
								'type'        => 'string',
							),
							array_merge(
								array( 'description' => __( 'Prompt content object.', 'wp-starter-plugin' ) ),
								$this->get_content_schema( array( Content::ROLE_SYSTEM ) )
							),
						),
					),
				),
				'additionalProperties' => true,
			),
			'content'      => array(
				'description' => __( 'Content data to pass to the model, including the prompt and optional history.', 'wp-starter-plugin' ),
				'type'        => array( 'string', 'object', 'array' ),
				'oneOf'       => array(
					array(
						'description' => __( 'Prompt text as a string.', 'wp-starter-plugin' ),
						'type'        => 'string',
					),
					array_merge(
						array( 'description' => __( 'Prompt including multi modal data such as files.', 'wp-starter-plugin' ) ),
						$this->get_parts_schema()
					),
					array_merge(
						array( 'description' => __( 'Prompt content object.', 'wp-starter-plugin' ) ),
						$this->get_content_schema( array( Content::ROLE_USER ) )
					),
					array(
						'description' => __( 'Array of contents, including history from previous user prompts and their model answers.', 'wp-starter-plugin' ),
						'type'        => 'array',
						'minItems'    => 1,
						'items'       => $this->get_content_schema( array( Content::ROLE_USER, Content::ROLE_MODEL ) ),
					),
				),
			),
		);
	}

	/**
	 * Returns the global route arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Global route arguments.
	 */
	protected function global_args(): array {
		return array(
			'args'   => array(
				'slug' => array(
					'description' => __( 'Unique service slug.', 'wp-starter-plugin' ),
					'type'        => 'string',
				),
			),
			'schema' => $this->get_schema(),
		);
	}

	/**
	 * Returns the response schema for the route.
	 *
	 * @since n.e.x.t
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
						'description' => __( 'Candidate content.', 'wp-starter-plugin' ),
						'readonly'    => true,
					),
					$this->get_content_schema(
						array( Content::ROLE_USER, Content::ROLE_MODEL, Content::ROLE_SYSTEM )
					)
				),
			),
			'additionalProperties' => true,
		);
	}

	/**
	 * Gets the REST schema that corresponds to a content Parts object.
	 *
	 * This must be in sync with the data structure of the {@see Parts} class.
	 *
	 * @since n.e.x.t
	 * @see Parts
	 *
	 * @return array<string, mixed> The schema for a Parts object.
	 */
	private function get_parts_schema(): array {
		return array(
			'type'     => 'array',
			'minItems' => 1,
			'items'    => array(
				'type'  => 'object',
				'oneOf' => array(
					array(
						'properties' => array(
							'text' => array(
								'description' => __( 'Prompt text content.', 'wp-starter-plugin' ),
								'type'        => 'string',
							),
						),
					),
					array(
						'properties' => array(
							'inlineData' => array(
								'description' => __( 'Inline data as part of the prompt, such as a file.', 'wp-starter-plugin' ),
								'type'        => 'object',
								'properties'  => array(
									'mimeType' => array(
										'description' => __( 'MIME type of the inline data.', 'wp-starter-plugin' ),
										'type'        => 'string',
									),
									'data'     => array(
										'description' => __( 'Base64-encoded data.', 'wp-starter-plugin' ),
										'type'        => 'string',
									),
								),
							),
						),
					),
					array(
						'properties' => array(
							'fileData' => array(
								'description' => __( 'Reference to a file as part of the prompt.', 'wp-starter-plugin' ),
								'type'        => 'object',
								'properties'  => array(
									'mimeType' => array(
										'description' => __( 'MIME type of the file data.', 'wp-starter-plugin' ),
										'type'        => 'string',
									),
									'fileUri'  => array(
										'description' => __( 'URI of the file.', 'wp-starter-plugin' ),
										'type'        => 'string',
									),
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Gets the REST schema that corresponds to a Content object.
	 *
	 * This must be in sync with the data structure of the {@see Content} class.
	 *
	 * @since n.e.x.t
	 * @see Content
	 *
	 * @param string[] $allowed_roles Which content roles to include in the schema.
	 * @return array<string, mixed> The schema for a Content object.
	 */
	private function get_content_schema( array $allowed_roles ): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'role'  => array(
					'type' => 'string',
					'enum' => $allowed_roles,
				),
				'parts' => $this->get_parts_schema(),
			),
		);
	}

	/**
	 * Parses the content data into one of the formats expected by the {@see With_Text_Generation::generate_text()} method.
	 *
	 * The implementation of this method goes hand in hand with the schema definitions for the 'content' parameter.
	 *
	 * @since n.e.x.t
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
