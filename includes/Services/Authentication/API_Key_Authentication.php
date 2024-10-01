<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Authentication\API_Key_Authentication
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Authentication;

use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;

/**
 * Class that represents an API key.
 *
 * @since n.e.x.t
 */
final class API_Key_Authentication implements Authentication {

	/**
	 * The API key.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $api_key;

	/**
	 * The HTTP header to use for the API key.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $header_name = 'Authorization';

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $api_key The API key.
	 */
	public function __construct( string $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Authenticates the given request with the credentials.
	 *
	 * @since n.e.x.t
	 *
	 * @param Request $request The request instance. Updated in place.
	 */
	public function authenticate( Request $request ): void {
		if ( 'authorization' === strtolower( $this->header_name ) ) {
			$request->add_header( $this->header_name, 'Bearer ' . $this->api_key );
		} else {
			$request->add_header( $this->header_name, $this->api_key );
		}
	}

	/**
	 * Sets the header name to use to add the credentials to a request.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $header_name The header name.
	 */
	public function set_header_name( string $header_name ): void {
		$this->header_name = $header_name;
	}

	/**
	 * Returns the option definitions needed to store the credentials.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $service_slug The service slug.
	 * @return array<string, array<string, mixed>> The option definitions.
	 */
	public static function get_option_definitions( string $service_slug ): array {
		$option_slug = sprintf( 'ais_%s_api_key', $service_slug );

		return array(
			$option_slug => array(
				'type'         => 'string',
				'default'      => '',
				'show_in_rest' => true,
				'autoload'     => true,
			),
		);
	}
}
