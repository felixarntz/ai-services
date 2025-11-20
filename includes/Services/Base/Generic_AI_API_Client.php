<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Base\Generic_AI_API_Client
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Base;

use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Traits\Generative_AI_API_Client_Trait;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Get_Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\JSON_Post_Request;

/**
 * Generic implementation of an AI API client, configured via constructor parameters.
 *
 * @since 0.7.0
 */
class Generic_AI_API_Client implements Generative_AI_API_Client {
	use Generative_AI_API_Client_Trait;

	/**
	 * The base URL for the API.
	 *
	 * @since 0.7.0
	 * @var string
	 */
	private $default_base_url;

	/**
	 * The API version.
	 *
	 * @since 0.7.0
	 * @var string
	 */
	private $default_api_version;

	/**
	 * The (human-readable) API name.
	 *
	 * @since 0.7.0
	 * @var string
	 */
	private $api_name;

	/**
	 * The request handler instance.
	 *
	 * @since 0.7.0
	 * @var Request_Handler
	 */
	private $request_handler;

	/**
	 * The authentication instance.
	 *
	 * @since 0.7.0
	 * @var Authentication|null
	 */
	private $authentication;

	/**
	 * Constructor.
	 *
	 * @since 0.7.0
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
		$this->default_base_url    = $default_base_url;
		$this->default_api_version = $default_api_version;
		$this->api_name            = $api_name;
		$this->request_handler     = $request_handler;
		$this->authentication      = $authentication;
	}

	/**
	 * Creates a GET request instance for the given parameters.
	 *
	 * @since 0.7.0
	 *
	 * @param string               $path            The path to the API endpoint, relative to the base URL and version.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_get_request( string $path, array $params, array $request_options = array() ): Request {
		$request_url     = $this->get_request_url( $path, $request_options );
		$request_options = $this->filter_request_options(
			$this->add_default_options( $request_options ),
			$request_url
		);

		$request = new Get_Request(
			$request_url,
			$params,
			$request_options
		);
		$this->authenticate_request( $request );
		return $request;
	}

	/**
	 * Creates a POST request instance for the given parameters.
	 *
	 * @since 0.7.0
	 *
	 * @param string               $path            The path to the API endpoint, relative to the base URL and version.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_post_request( string $path, array $params, array $request_options = array() ): Request {
		$request_url     = $this->get_request_url( $path, $request_options );
		$request_options = $this->filter_request_options(
			$this->add_default_options( $request_options ),
			$request_url
		);

		$request = new JSON_Post_Request(
			$request_url,
			$params,
			$request_options
		);
		$this->authenticate_request( $request );
		return $request;
	}

	/**
	 * Gets the request URL for the specified model and task.
	 *
	 * @since 0.7.0
	 *
	 * @param string               $path            The path to the API endpoint, relative to the base URL and version.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return string The request URL.
	 */
	protected function get_request_url( string $path, array $request_options = array() ): string {
		$base_url    = $request_options['base_url'] ?? $this->default_base_url;
		$api_version = $request_options['api_version'] ?? $this->default_api_version;
		$path        = ltrim( $path, '/' );

		if ( isset( $request_options['stream'] ) && $request_options['stream'] && ! str_ends_with( $path, '?alt=sse' ) ) {
			$path .= '?alt=sse';
		}

		if ( '' === $api_version ) {
			return "{$base_url}/{$path}";
		}

		return "{$base_url}/{$api_version}/{$path}";
	}

	/**
	 * Adds additional default request options to the given request options.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $request_options The request options.
	 * @return array<string, mixed> The updated request options.
	 */
	protected function add_default_options( array $request_options ): array {
		if ( ! isset( $request_options['timeout'] ) ) {
			$request_options['timeout'] = 30;
		}
		return $request_options;
	}

	/**
	 * Authenticates the request, if an authentication instance is set.
	 *
	 * @since 0.7.0
	 *
	 * @param Request $request The request to authenticate.
	 */
	final protected function authenticate_request( Request $request ): void {
		if ( $this->authentication ) {
			$this->authentication->authenticate( $request );
		}
	}

	/**
	 * Returns the human readable API name (without the "API" suffix).
	 *
	 * @since 0.7.0
	 *
	 * @return string The API name.
	 */
	final protected function get_api_name(): string {
		return $this->api_name;
	}

	/**
	 * Returns the request handler instance to use for requests.
	 *
	 * @since 0.7.0
	 *
	 * @return Request_Handler The request handler instance.
	 */
	final protected function get_request_handler(): Request_Handler {
		return $this->request_handler;
	}

	/**
	 * Filters the request options, with awareness of the request URL.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $request_options The request options.
	 * @param string               $request_url    The request URL.
	 * @return array<string, mixed> The filtered request options.
	 */
	private function filter_request_options( array $request_options, string $request_url ): array {
		if ( isset( $request_options['timeout'] ) ) {
			$timeout = $request_options['timeout'];

			/**
			 * Filters the request timeout to use for an API request to an AI service.
			 *
			 * @since 0.7.0
			 *
			 * @param int    $timeout     The request timeout in seconds.
			 * @param string $request_url The request URL.
			 */
			$request_options['timeout'] = (int) apply_filters( 'ai_services_request_timeout', $timeout, $request_url );

			// If the filtered timeout is invalid, use the original value.
			if ( $request_options['timeout'] <= 0 ) {
				$request_options['timeout'] = $timeout;
			}
		}

		return $request_options;
	}
}
