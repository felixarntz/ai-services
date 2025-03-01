<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\History_REST_Resource_Schema
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Services\API\Types\History;
use Felix_Arntz\AI_Services\Services\Entities\History_Entity;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Resource_Schema;

/**
 * Class representing the schema for a history resource in the REST API.
 *
 * @since n.e.x.t
 */
class History_REST_Resource_Schema extends Abstract_REST_Resource_Schema {

	/**
	 * Prepares the given resource for inclusion in a response, based on the given fields.
	 *
	 * @since n.e.x.t
	 *
	 * @param History_Entity $entity The entity to prepare.
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
	 * @param History_Entity $entity The entity to prepare links for.
	 * @return array<string, array<string, mixed>> Links for the given resource.
	 */
	protected function prepare_resource_links( $entity ): array {
		return array(
			'self'       => array(
				'href' => $this->get_route_url(
					str_replace(
						array(
							'(?P<feature>[\w-]+)',
							'(?P<slug>[\w-]+)',
						),
						array(
							$entity->get_field_value( 'feature' ),
							$entity->get_field_value( 'slug' ),
						),
						History_Get_REST_Route::BASE
					)
				),
			),
			'collection' => array(
				'href' => $this->get_collection_route_url(
					array( 'feature' => $entity->get_field_value( 'feature' ) )
				),
			),
		);
	}

	/**
	 * Returns the full URL to the resource's collection route.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $params Optional. Current request parameters. Default empty array.
	 * @return string Full collection route URL.
	 */
	protected function get_collection_route_url( array $params = array() ): string {
		return $this->get_route_url(
			str_replace(
				'(?P<feature>[\w-]+)',
				$params['feature'] ?? '',
				History_List_REST_Route::BASE
			)
		);
	}

	/**
	 * Returns the internal resource schema definition.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Internal resource schema definition.
	 */
	protected function schema(): array {
		return array_merge(
			array(
				'$schema' => 'http://json-schema.org/draft-04/schema#',
				'title'   => 'history',
			),
			History::get_json_schema()
		);
	}
}
