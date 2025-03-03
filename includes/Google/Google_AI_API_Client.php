<?php
/**
 * Class Felix_Arntz\AI_Services\Google\Google_AI_API_Client
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Google;

use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Traits\Generative_AI_API_Client_Trait;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Get_Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\JSON_Post_Request;

/**
 * Class to interact directly with the Google Generative Language API.
 *
 * @since 0.1.0
 */
class Google_AI_API_Client implements Generative_AI_API_Client {
	use Generative_AI_API_Client_Trait;

	const DEFAULT_BASE_URL    = 'https://generativelanguage.googleapis.com';
	const DEFAULT_API_VERSION = 'v1beta';

	/**
	 * The Google Generative Language API key authentication.
	 *
	 * @since 0.1.0
	 * @var Authentication
	 */
	private $authentication;

	/**
	 * The HTTP instance to use for requests.
	 *
	 * @since 0.1.0
	 * @var HTTP
	 */
	private $http;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Authentication $authentication The authentication credentials.
	 * @param HTTP           $http           The HTTP instance to use for requests.
	 */
	public function __construct( Authentication $authentication, HTTP $http ) {
		$this->authentication = $authentication;
		$this->http           = $http;

		$this->authentication->set_header_name( 'X-Goog-Api-Key' );
	}

	/**
	 * Creates a request instance to list the available models with their information.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $params          Optional. The request parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_list_models_request( array $params = array(), array $request_options = array() ): Request {
		return $this->create_get_request( 'models', $params, $request_options );
	}

	/**
	 * Creates a request instance to generate content using the specified model.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_generate_content_request( string $model, array $params, array $request_options = array() ): Request {
		if ( ! str_contains( $model, '/' ) ) {
			$model = 'models/' . $model;
		}
		return $this->create_post_request( "{$model}:generateContent", $params, $request_options );
	}

	/**
	 * Creates a stream request instance to generate content using the specified model.
	 *
	 * @since 0.3.0
	 *
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_stream_generate_content_request( string $model, array $params, array $request_options = array() ): Request {
		if ( ! str_contains( $model, '/' ) ) {
			$model = 'models/' . $model;
		}
		$request_options['stream'] = true;
		return $this->create_post_request( "{$model}:streamGenerateContent", $params, $request_options );
	}

	/**
	 * Creates a request instance to generate images using the specified model.
	 *
	 * @since 0.5.0
	 *
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	public function create_generate_images_request( string $model, array $params, array $request_options = array() ): Request {
		if ( ! str_contains( $model, '/' ) ) {
			$model = 'models/' . $model;
		}
		return $this->create_post_request( "{$model}:predict", $params, $request_options );
	}

	/**
	 * Returns the HTTP instance to use for requests.
	 *
	 * @since 0.1.0
	 *
	 * @return HTTP The HTTP instance.
	 */
	protected function get_http(): HTTP {
		return $this->http;
	}

	/**
	 * Returns the human readable API name (without the "API" suffix).
	 *
	 * @since 0.1.0
	 *
	 * @return string The API name.
	 */
	protected function get_api_name(): string {
		return 'Google Generative Language';
	}

	/**
	 * Creates a GET request instance for the given parameters.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $path            The path to the API endpoint, relative to the base URL and version.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	private function create_get_request( string $path, array $params, array $request_options = array() ): Request {
		$request = new Get_Request(
			$this->get_request_url( $path, $request_options ),
			$params,
			$this->add_request_headers( $request_options )
		);
		$this->add_default_options( $request );
		$this->authentication->authenticate( $request );
		return $request;
	}

	/**
	 * Creates a POST request instance for the given parameters.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $path            The path to the API endpoint, relative to the base URL and version.
	 * @param array<string, mixed> $params          The request parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return Request The request instance.
	 */
	private function create_post_request( string $path, array $params, array $request_options = array() ): Request {
		$request = new JSON_Post_Request(
			$this->get_request_url( $path, $request_options ),
			$params,
			$this->add_request_headers( $request_options )
		);
		$this->add_default_options( $request );
		$this->authentication->authenticate( $request );
		return $request;
	}

	/**
	 * Gets the request URL for the specified model and task.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $path            The path to the API endpoint, relative to the base URL and version.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 * @return string The request URL.
	 */
	private function get_request_url( string $path, array $request_options = array() ): string {
		$base_url    = $request_options['base_url'] ?? self::DEFAULT_BASE_URL;
		$api_version = $request_options['api_version'] ?? self::DEFAULT_API_VERSION;
		$path        = ltrim( $path, '/' );

		if ( isset( $request_options['stream'] ) && $request_options['stream'] && ! str_ends_with( $path, '?alt=sse' ) ) {
			$path .= '?alt=sse';
		}

		return "{$base_url}/{$api_version}/{$path}";
	}

	/**
	 * Adds the required request headers to the request options.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $request_options The request options.
	 * @return array<string, mixed> The updated request options.
	 */
	private function add_request_headers( array $request_options ): array {
		if ( ! isset( $request_options['headers'] ) ) {
			$request_options['headers'] = array();
		}
		$request_options['headers']['X-Goog-Api-Client'] = 'ai-services/' . AI_SERVICES_VERSION;
		return $request_options;
	}
}
