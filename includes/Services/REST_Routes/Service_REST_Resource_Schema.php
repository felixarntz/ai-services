<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\REST_Routes\Service_REST_Resource_Schema
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\REST_Routes;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Resource_Schema;

/**
 * Class representing the schema for a service resource in the REST API.
 *
 * @since n.e.x.t
 */
class Service_REST_Resource_Schema extends Abstract_REST_Resource_Schema {

	/**
	 * Prepares the given resource for inclusion in a response, based on the given fields.
	 *
	 * @since n.e.x.t
	 *
	 * @param Service_Entity $entity The entity to prepare.
	 * @param string[]       $fields Fields to be included in the response.
	 * @return array<string, mixed> Associative array of resource data.
	 */
	protected function prepare_resource_fields( $entity, array $fields ): array {
		$data = array();
		foreach ( $fields as $field ) {
			$data[ $field ] = $entity->get_field_value( $field );
		}
		return $data;
	}

	/**
	 * Prepares links for the given resource.
	 *
	 * @since n.e.x.t
	 *
	 * @param Service_Entity $entity The entity to prepare links for.
	 * @return array<string, array<string, mixed>> Links for the given resource.
	 */
	protected function prepare_resource_links( $entity ): array {
		return array(
			'self'       => array(
				'href' => $this->get_route_url( str_replace( '(?P<slug>[\w-]+)', $entity->get_field_value( 'slug' ), Service_Get_REST_Route::BASE ) ),
			),
			'collection' => array(
				'href' => $this->get_route_url( Service_List_REST_Route::BASE ),
			),
		);
	}

	/**
	 * Returns the full URL to the resource's collection route.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Full collection route URL.
	 */
	protected function get_collection_route_url(): string {
		return $this->get_route_url( Service_List_REST_Route::BASE );
	}

	/**
	 * Returns the internal resource schema definition.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Internal resource schema definition.
	 */
	protected function schema(): array {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'service',
			'type'       => 'object',
			'properties' => array(
				'slug'               => array(
					'description' => __( 'Unique service slug.', 'wp-starter-plugin' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'               => array(
					'description' => __( 'User-facing service name.', 'wp-starter-plugin' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'is_available'       => array(
					'description' => __( 'Whether the service is fully configured and available.', 'wp-starter-plugin' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'available_models'   => array(
					'description' => __( 'List of the available model slugs (empty if the service is not available).', 'wp-starter-plugin' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'items'       => array(
						'type' => 'string',
					),
				),
				'has_forced_api_key' => array(
					'description' => __( 'Whether the service API key is force-set (i.e. not modifiable by changing the option value).', 'wp-starter-plugin' ),
					'type'        => 'boolean',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
			),
		);
	}
}
