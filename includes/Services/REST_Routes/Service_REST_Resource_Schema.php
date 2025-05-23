<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\Service_REST_Resource_Schema
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
use Felix_Arntz\AI_Services\Services\Entities\Service_Entity;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Resource_Schema;

/**
 * Class representing the schema for a service resource in the REST API.
 *
 * @since 0.1.0
 */
class Service_REST_Resource_Schema extends Abstract_REST_Resource_Schema {

	/**
	 * Prepares the given resource for inclusion in a response, based on the given fields.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
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
				'href' => $this->get_collection_route_url(),
			),
		);
	}

	/**
	 * Returns the full URL to the resource's collection route.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $params Optional. Current request parameters. Default empty array.
	 * @return string Full collection route URL.
	 */
	protected function get_collection_route_url( array $params = array() ): string {
		return $this->get_route_url( Service_List_REST_Route::BASE );
	}

	/**
	 * Returns the internal resource schema definition.
	 *
	 * @since 0.1.0
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
					'description' => __( 'Unique service slug.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'metadata'           => Service_Metadata::get_json_schema(),
				'is_available'       => array(
					'description' => __( 'Whether the service is fully configured and available.', 'ai-services' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'available_models'   => array(
					'description'          => __( 'Metadata for each model, mapped by model slug (or empty if the service is not available).', 'ai-services' ),
					'type'                 => 'object',
					'context'              => array( 'view', 'edit' ),
					'readonly'             => true,
					'properties'           => array(),
					'additionalProperties' => Model_Metadata::get_json_schema(),
				),
				'has_forced_api_key' => array(
					'description' => __( 'Whether the service API key is force-set (i.e. not modifiable by changing the option value).', 'ai-services' ),
					'type'        => 'boolean',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
			),
		);
	}
}
