<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Entities\Service_Entity_Query
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Entities;

use Felix_Arntz\AI_Services\Services\Services_API;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Entities\Contracts\Entity_Query;

/**
 * Class representing a service entity query for the REST API.
 *
 * @since 0.1.0
 * @since n.e.x.t Moved from Felix_Arntz\AI_Services\Services\REST_Routes namespace.
 */
class Service_Entity_Query implements Entity_Query {

	/**
	 * The services API instance.
	 *
	 * @since 0.1.0
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * Query arguments.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	private $query_args;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Services_API         $services_api The services API instance.
	 * @param array<string, mixed> $query_args   {
	 *     The query arguments.
	 *
	 *     @type int $number Number of services to limit results to. Default 10.
	 *     @type int $offset Offset at which to start with the results. Default 0.
	 * }
	 */
	public function __construct( Services_API $services_api, array $query_args ) {
		$this->services_api = $services_api;
		$this->query_args   = $this->parse_defaults( $query_args );
	}

	/**
	 * Queries services.
	 *
	 * @since 0.1.0
	 *
	 * @return Service_Entity[] List of service entities.
	 */
	public function get_entities(): array {
		$slugs = $this->services_api->get_registered_service_slugs();

		// For now the only supported orderby is 'slug', so we can just sort the slugs.
		if ( 'DESC' === $this->query_args['order'] ) {
			rsort( $slugs );
		} else {
			sort( $slugs );
		}

		$slugs = array_slice( $slugs, $this->query_args['offset'], $this->query_args['number'] );

		return array_map(
			function ( $slug ) {
				return new Service_Entity( $this->services_api, $slug );
			},
			$slugs
		);
	}

	/**
	 * Queries service IDs.
	 *
	 * @since 0.1.0
	 *
	 * @return int[] Empty array, as services do not have IDs.
	 */
	public function get_ids(): array {
		return array();
	}

	/**
	 * Queries the service count.
	 *
	 * @since 0.1.0
	 *
	 * @return int Service count.
	 */
	public function get_count(): int {
		$slugs = $this->services_api->get_registered_service_slugs();
		return count( $slugs );
	}

	/**
	 * Parses and sanitizes the given query arguments.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $query_args Query arguments.
	 * @return array<string, mixed> Query arguments parsed with defaults.
	 */
	private function parse_defaults( array $query_args ): array {
		$defaults = array(
			'orderby' => 'slug',
			'order'   => 'ASC',
			'number'  => 10,
			'offset'  => 0,
		);

		// Parse defaults and strip any keys that are not allowed.
		$query_args = wp_parse_args( $query_args, $defaults );
		$query_args = wp_array_slice_assoc( $query_args, array_keys( $defaults ) );

		$query_args['order'] = strtoupper( $query_args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		return $query_args;
	}
}
