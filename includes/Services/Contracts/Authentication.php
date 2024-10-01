<?php
/**
 * Interface Felix_Arntz\AI_Services\Services\Contracts\Authentication
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Contracts;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;

/**
 * Interface for a class representing authentication credentials of a certain kind for an API client.
 *
 * @since n.e.x.t
 */
interface Authentication {

	/**
	 * Authenticates the given request with the credentials.
	 *
	 * @since n.e.x.t
	 *
	 * @param Request $request The request instance. Updated in place.
	 */
	public function authenticate( Request $request ): void;

	/**
	 * Sets the header name to use to add the credentials to a request.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $header_name The header name.
	 */
	public function set_header_name( string $header_name ): void;

	/**
	 * Returns the option definitions needed to store the credentials.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $service_slug The service slug.
	 * @return array<string, array<string, mixed>> The option definitions.
	 */
	public static function get_option_definitions( string $service_slug ): array;
}
