<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\REST_Routes\Service_Generate_Content_REST_Route
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\REST_Routes;

use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Services_API;
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
	const BASE    = '/services/(?P<slug>[\w-]+):generate-content';
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

		$service = $this->services_api->get_service( $request['slug'] );

		if ( isset( $request['model'] ) ) {
			$model = $request['model'];
		} else {
			// For now, we just use the first model available. TODO: Improve this later, e.g. by specifying a default.
			try {
				$model_slugs = $service->list_models();
				$model       = $model_slugs[0];
			} catch ( Generative_AI_Exception $e ) {
				throw REST_Exception::create(
					'rest_cannot_determine_model',
					esc_html__( 'Determining the model to use failed.', 'wp-starter-plugin' ),
					500
				);
			}
		}

		try {
			// TODO: Allow processing model_params, e.g. to parse data into classes where applicable.
			$model = $service->get_model( $model, $request['model_params'] ?? array() );
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

		try {
			// TODO: Parse content data into the correct classes (detect data and rely on from_array() methods).
			$candidates = $model->generate_content( $request['content'] );
		} catch ( Generative_AI_Exception $e ) {
			throw REST_Exception::create(
				'rest_generating_content_failed',
				sprintf(
					/* translators: %s: original error message */
					esc_html__( 'Generating content failed: %s', 'wp-starter-plugin' ),
					esc_html( $e->getMessage() )
				),
				500
			);
		}

		return rest_ensure_response( $candidates->to_array() );
	}

	/**
	 * Returns the route specific arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Route arguments.
	 */
	protected function args(): array {
		// TODO: Complete this.
		return array(
			'model'        => array(
				'description' => __( 'Model slug.', 'wp-starter-plugin' ),
				'type'        => 'string',
			),
			'model_params' => array(
				'description' => __( 'Model parameters.', 'wp-starter-plugin' ),
				'type'        => 'array',
			),
			'content'      => array(
				'description' => __( 'Content data.', 'wp-starter-plugin' ),
				'type'        => 'array', // TODO: Or a string.
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
		// TODO: Complete this.
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'candidate',
			'type'       => 'object',
			'properties' => array(
				'content' => array(
					'description' => __( 'Candidate content.', 'wp-starter-plugin' ),
					'type'        => 'array',
					'readonly'    => true,
				),
			),
		);
	}
}
