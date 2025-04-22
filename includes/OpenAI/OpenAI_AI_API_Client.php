<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_API_Client
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Felix_Arntz\AI_Services\Services\Contracts\Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Traits\Generative_AI_API_Client_Trait;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Get_Request;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\JSON_Post_Request;

/**
 * Class to interact directly with the OpenAI API.
 *
 * @since 0.1.0
 */
class OpenAI_AI_API_Client implements Generative_AI_API_Client {
	use Generative_AI_API_Client_Trait;

	const DEFAULT_BASE_URL    = 'https://api.openai.com';
	const DEFAULT_API_VERSION = 'v1';

	/**
	 * The OpenAI API key authentication.
	 *
	 * @since 0.1.0
	 * @var Authentication
	 */
	private $authentication;

	/**
	 * The request handler instance.
	 *
	 * @since 0.1.0
	 * @var Request_Handler
	 */
	private $request_handler;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Authentication  $authentication  The authentication credentials.
	 * @param Request_Handler $request_handler The request handler instance.
	 */
	public function __construct( Authentication $authentication, Request_Handler $request_handler ) {
		$this->authentication  = $authentication;
		$this->request_handler = $request_handler;
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
		$params['model'] = $model;
		return $this->create_post_request( 'chat/completions', $params, $request_options );
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
		$params['model']           = $model;
		$params['stream']          = true;
		$request_options['stream'] = true;
		return $this->create_post_request( 'chat/completions', $params, $request_options );
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
		$params['model'] = $model;
		return $this->create_post_request( 'images/generations', $params, $request_options );
	}

	/**
	 * Returns the request handler instance to use for requests.
	 *
	 * @since 0.1.0
	 * @since 0.6.0 Renamed from `get_http()`.
	 *
	 * @return Request_Handler The request handler instance.
	 */
	protected function get_request_handler(): Request_Handler {
		return $this->request_handler;
	}

	/**
	 * Returns the human readable API name (without the "API" suffix).
	 *
	 * @since 0.1.0
	 *
	 * @return string The API name.
	 */
	protected function get_api_name(): string {
		return 'OpenAI';
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
			$request_options
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
			$request_options
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

		return "{$base_url}/{$api_version}/{$path}";
	}
}
