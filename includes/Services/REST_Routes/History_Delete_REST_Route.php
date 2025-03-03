<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\History_Delete_REST_Route
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\Entities\History_Entity;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Route;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Exception\REST_Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class representing the REST API route for deleting a history.
 *
 * @since 0.5.0
 */
class History_Delete_REST_Route extends Abstract_REST_Route {
	const BASE    = '/features/(?P<feature>[\w-]+)/histories/(?P<slug>[\w-]+)';
	const METHODS = WP_REST_Server::DELETABLE;

	/**
	 * The current user instance.
	 *
	 * @since 0.5.0
	 * @var Current_User
	 */
	private $current_user;

	/**
	 * Relevant resource schema.
	 *
	 * @since 0.5.0
	 * @var History_REST_Resource_Schema
	 */
	private $resource_schema;

	/**
	 * Constructor.
	 *
	 * @since 0.5.0
	 *
	 * @param Current_User                 $current_user    The current user service.
	 * @param History_REST_Resource_Schema $resource_schema The relevant resource schema.
	 */
	public function __construct( Current_User $current_user, History_REST_Resource_Schema $resource_schema ) {
		$this->current_user    = $current_user;
		$this->resource_schema = $resource_schema;

		parent::__construct();
	}

	/**
	 * Returns the route base.
	 *
	 * @since 0.5.0
	 *
	 * @return string Route base.
	 */
	protected function base(): string {
		return self::BASE;
	}

	/**
	 * Returns the route methods, as a comma-separated string.
	 *
	 * @since 0.5.0
	 *
	 * @return string Route methods, as a comma-separated string.
	 */
	protected function methods(): string {
		return self::METHODS;
	}

	/**
	 * Checks the required permissions for the given request and throws an exception if they aren't met.
	 *
	 * @since 0.5.0
	 *
	 * @param WP_REST_Request $request WordPress REST request object, including parameters.
	 *
	 * @throws REST_Exception Thrown when the permissions aren't met, or when a REST error occurs.
	 */
	protected function check_permissions( WP_REST_Request $request ): void /* @phpstan-ignore-line */ {
		// Only users that can access AI services should be able to access histories.
		if ( ! $this->current_user->has_cap( 'ais_access_services' ) ) {
			throw REST_Exception::create(
				'rest_cannot_view',
				esc_html__( 'Sorry, you are not allowed to access services.', 'ai-services' ),
				$this->current_user->is_logged_in() ? 403 : 401
			);
		}
	}

	/**
	 * Handles the given request and returns a response.
	 *
	 * @since 0.5.0
	 *
	 * @param WP_REST_Request $request WordPress REST request object, including parameters.
	 * @return WP_REST_Response WordPress REST response object.
	 *
	 * @throws REST_Exception Thrown when a REST error occurs.
	 */
	protected function handle_request( WP_REST_Request $request ): WP_REST_Response /* @phpstan-ignore-line */ {
		$history_persistence = Helpers::history_persistence();

		$history = $history_persistence->load_history( $request['feature'], $request['slug'] );
		if ( null === $history ) {
			throw REST_Exception::create(
				'rest_history_invalid_slug',
				esc_html__( 'Invalid history slug.', 'ai-services' ),
				404
			);
		}

		if ( ! $history_persistence->clear_history( $request['feature'], $request['slug'] ) ) {
			throw REST_Exception::create(
				'rest_history_clear_failed',
				esc_html__( 'Failed to clear history.', 'ai-services' ),
				500
			);
		}

		// Double check that history was deleted.
		if ( null !== $history_persistence->load_history( $request['feature'], $request['slug'] ) ) {
			throw REST_Exception::create(
				'rest_history_clear_failed',
				esc_html__( 'Failed to clear history.', 'ai-services' ),
				500
			);
		}

		// Force 'edit' context, then prepare the history for response in a 'previous' key.
		$request->set_param( 'context', 'edit' );
		$response = $this->resource_schema->prepare_resource(
			new History_Entity( $history ),
			$request
		);
		return rest_ensure_response(
			array(
				'deleted'  => true,
				'previous' => $response->get_data(),
			)
		);
	}

	/**
	 * Returns the route specific arguments.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> Route arguments.
	 */
	protected function args(): array {
		return array();
	}

	/**
	 * Returns the global route arguments.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> Global route arguments.
	 */
	protected function global_args(): array {
		return array(
			'args'        => array(
				'feature' => array(
					'description' => __( 'Feature identifier.', 'ai-services' ),
					'type'        => 'string',
				),
				'slug'    => array(
					'description' => __( 'Unique history identifier within the feature.', 'ai-services' ),
					'type'        => 'string',
				),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this->resource_schema, 'get_public_schema' ),
		);
	}
}
