<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\History_List_REST_Route
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\Entities\History_Entity_Query;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Route;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Exception\REST_Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class representing the REST API route for listing histories.
 *
 * @since 0.5.0
 */
class History_List_REST_Route extends Abstract_REST_Route {
	const BASE    = '/features/(?P<feature>[\w-]+)/histories';
	const METHODS = WP_REST_Server::READABLE;

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
		$query_args = $this->request_pagination_to_query_args( $request );
		foreach ( array_keys( $this->get_query_args() ) as $arg ) {
			if ( isset( $request[ $arg ] ) ) {
				$query_args[ $arg ] = $request[ $arg ];
			}
		}

		$history_persistence = Helpers::history_persistence();

		return $this->resource_schema->prepare_resources_for_query(
			new History_Entity_Query( $history_persistence, $query_args ),
			$request
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
		return array_merge(
			$this->resource_schema->get_schema_args( self::METHODS ),
			$this->get_pagination_args(),
			$this->get_query_args()
		);
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
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this->resource_schema, 'get_public_schema' ),
		);
	}

	/**
	 * Returns the route specific arguments that map directly to a service query.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, mixed> Service query arguments for the route.
	 */
	protected function get_query_args(): array {
		static $args = null;

		if ( null === $args ) {
			$args = array(
				'order'   => array(
					'description' => __( 'Order sort attribute ascending or descending.', 'ai-services' ),
					'type'        => 'string',
					'default'     => 'asc',
					'enum'        => array( 'asc', 'desc' ),
				),
				'orderby' => array(
					'description' => __( 'Sort results by history attribute.', 'ai-services' ),
					'type'        => 'string',
					'default'     => 'created',
					'enum'        => array( 'slug', 'created', 'lastUpdated' ),
				),
			);
		}

		return $args;
	}
}
