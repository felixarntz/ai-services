<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\REST_Routes\Service_Entity
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\REST_Routes;

use Vendor_NS\WP_Starter_Plugin\Services\Services_API;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Entities\Contracts\Entity;

/**
 * Class representing a service entity for the REST API.
 *
 * @since n.e.x.t
 */
class Service_Entity implements Entity {

	/**
	 * The services API instance.
	 *
	 * @since n.e.x.t
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * The service slug.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $slug;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Services_API $services_api The services API instance.
	 * @param string       $slug         The service slug.
	 */
	public function __construct( Services_API $services_api, string $slug ) {
		$this->services_api = $services_api;
		$this->slug         = $slug;
	}

	/**
	 * Gets the entity ID.
	 *
	 * @since n.e.x.t
	 *
	 * @return int The entity ID.
	 */
	public function get_id(): int {
		return 0; // Unused, as services use slugs instead of numeric identifiers.
	}

	/**
	 * Checks whether the entity is publicly accessible.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the entity is public, false otherwise.
	 */
	public function is_public(): bool {
		return false;
	}

	/**
	 * Gets the entity's primary URL.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Primary entity URL, or empty string if none.
	 */
	public function get_url(): string {
		return '';
	}

	/**
	 * Gets the entity's edit URL, if the current user is able to edit it.
	 *
	 * @since n.e.x.t
	 *
	 * @return string URL to edit the entity, or empty string if unable to edit.
	 */
	public function get_edit_url(): string {
		return '';
	}

	/**
	 * Gets the value for the given field of the entity.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $field The field identifier.
	 * @return mixed Value for the field, `null` if not set.
	 */
	public function get_field_value( string $field ) {
		switch ( $field ) {
			case 'slug':
				return $this->slug;
			case 'name':
				return $this->services_api->get_service_name( $this->slug );
			case 'is_available':
				return $this->services_api->is_service_available( $this->slug );
			case 'has_forced_api_key':
				return $this->has_forced_api_key();
		}
		return null;
	}

	/**
	 * Checks whether the service has a forced API key (i.e. the API key option is being overridden).
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the service has a forced API key, false otherwise.
	 */
	private function has_forced_api_key() {
		$option = sprintf( 'wpsp_%s_api_key', $this->slug );

		// These filters are part of get_option() and allow plugins to short-circuit the option retrieval.
		$pre = apply_filters( "pre_option_{$option}", false, $option, '' );
		$pre = apply_filters( 'pre_option', $pre, $option, '' );

		return false !== $pre;
	}
}
