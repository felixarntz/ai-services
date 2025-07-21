<?php
/**
 * Class Felix_Arntz\AI_Services\Anthropic\Anthropic_AI_API_Client
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Anthropic;

use Felix_Arntz\AI_Services\Services\Base\Generic_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;

/**
 * Class to interact directly with the Anthropic API.
 *
 * @since 0.1.0
 * @since n.e.x.t Now extends `Generic_AI_API_Client`.
 */
class Anthropic_AI_API_Client extends Generic_AI_API_Client {

	/**
	 * The Anthropic API version used in the `anthropic-version` header.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	const ANTHROPIC_API_VERSION = '2023-06-01';

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string              $default_base_url    The default base URL for the API.
	 * @param string              $default_api_version The default API version.
	 * @param string              $api_name            The (human-readable) API name.
	 * @param Request_Handler     $request_handler     The request handler instance.
	 * @param Authentication|null $authentication      Optional. The authentication instance. Default null.
	 */
	public function __construct(
		string $default_base_url,
		string $default_api_version,
		string $api_name,
		Request_Handler $request_handler,
		?Authentication $authentication = null
	) {
		// Set custom header name for Anthropic API key authentication.
		if ( $authentication ) {
			$authentication->set_header_name( 'x-api-key' );
		}

		parent::__construct( $default_base_url, $default_api_version, $api_name, $request_handler, $authentication );
	}

	/**
	 * Adds additional default request options to the given request options.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $request_options The request options.
	 * @return array<string, mixed> The updated request options.
	 */
	protected function add_default_options( array $request_options ): array {
		$request_options = parent::add_default_options( $request_options );

		if ( ! isset( $request_options['headers'] ) ) {
			$request_options['headers'] = array();
		}
		$request_options['headers']['anthropic-version'] = self::ANTHROPIC_API_VERSION;

		return $request_options;
	}
}
