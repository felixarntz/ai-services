<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Service_Registration
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services;

use Felix_Arntz\AI_Services\Services\Authentication\API_Key_Authentication;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Decorators\AI_Service_Decorator;
use Felix_Arntz\AI_Services\Services\HTTP\HTTP_With_Streams;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Key_Value_Repository;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\Contracts\Request_Handler;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Repository;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class representing a service registration.
 *
 * This is an internal class and NOT the actual service.
 *
 * @since 0.1.0
 */
final class Service_Registration {

	/**
	 * The service slug.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $slug;

	/**
	 * The service creator.
	 *
	 * @since 0.1.0
	 * @var callable
	 */
	private $creator;

	/**
	 * The service arguments.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	private $args;

	/**
	 * The authentication option slugs.
	 *
	 * @since 0.1.0
	 * @var string[]
	 */
	private $authentication_option_slugs;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 * @since n.e.x.t The service argument keys were updated.
	 *
	 * @param string               $slug    The service slug. Must only contain lowercase letters, numbers, hyphens.
	 * @param callable             $creator The service creator. Receives the Authentication instance as first
	 *                                      parameter, the request handler instance as second parameter, and must
	 *                                      return a Generative_AI_Service instance.
	 * @param array<string, mixed> $args    {
	 *     Optional. The service arguments. Default empty array.
	 *
	 *     @type string               $name            The service name. Default is the slug with spaces and uppercase
	 *                                                 first letters.
	 *     @type string               $credentials_url The URL to manage credentials for the service. Default empty
	 *                                                 string.
	 *     @type bool                 $allow_override  Whether the service can be overridden by another service with
	 *                                                 the same slug. Default true.
	 *     @type Request_Handler      $request_handler The request handler instance. Default is a new HTTP_With_Streams
	 *                                                 instance.
	 *     @type Container            $container       The container instance with data for the API key options.
	 *                                                 Default is a new Option_Container instance.
	 *     @type Key_Value_Repository $repository      The repository instance to read API keys Default is a new
	 *                                                 Option_Repository instance.
	 * }
	 */
	public function __construct( string $slug, callable $creator, array $args = array() ) {
		$this->validate_slug( $slug );

		$this->slug    = $slug;
		$this->creator = $creator;
		$this->args    = $this->parse_args( $args );

		$option_definitions = API_Key_Authentication::get_option_definitions( $slug );

		$this->authentication_option_slugs = array();
		foreach ( $option_definitions as $option_slug => $option_args ) {
			$this->authentication_option_slugs[]     = $option_slug;
			$this->args['container'][ $option_slug ] = function () use ( $option_slug, $option_args ) {
				return new Option(
					$this->args['repository'],
					$option_slug,
					$option_args
				);
			};
		}
	}

	/**
	 * Gets the service slug.
	 *
	 * @since 0.1.0
	 *
	 * @return string The service slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Gets the authentication option instances.
	 *
	 * @since 0.1.0
	 *
	 * @return Option[] The authentication option instances.
	 */
	public function get_authentication_options(): array {
		return array_map(
			function ( string $option_slug ) {
				return $this->args['container'][ $option_slug ];
			},
			$this->authentication_option_slugs
		);
	}

	/**
	 * Gets the authentication option slugs.
	 *
	 * @since 0.1.0
	 *
	 * @return string[] The authentication option slugs.
	 */
	public function get_authentication_option_slugs(): array {
		return $this->authentication_option_slugs;
	}

	/**
	 * Creates a new instance of the service.
	 *
	 * @since 0.1.0
	 *
	 * @return Generative_AI_Service The service instance.
	 *
	 * @throws RuntimeException Thrown if no API key is set for the service or if the service creator does not return a
	 *                          Generative_AI_Service instance.
	 */
	public function create_instance(): Generative_AI_Service {
		$authentication_options = $this->get_authentication_options();

		// For now an API key is the only authentication method supported.
		$api_key = $authentication_options[0]->get_value();
		if ( ! $api_key ) {
			throw new RuntimeException(
				esc_html(
					sprintf(
						/* translators: %s: service slug */
						__( 'Cannot instantiate service %s without an API key.', 'ai-services' ),
						$this->slug
					)
				)
			);
		}

		$api_key_authentication = new API_Key_Authentication( $api_key );

		$instance = ( $this->creator )( $api_key_authentication, $this->args['request_handler'] );
		if ( ! $instance instanceof Generative_AI_Service ) {
			throw new RuntimeException(
				esc_html(
					sprintf(
						/* translators: %s: service slug */
						__( 'The service creator for %s must return an instance of Generative_AI_Service.', 'ai-services' ),
						$this->slug
					)
				)
			);
		}
		if ( $instance->get_service_slug() !== $this->slug ) {
			throw new RuntimeException(
				esc_html(
					sprintf(
						/* translators: 1: service slug registered, 2: service slug returned by the class */
						__( 'The service creator for %1$s must return an instance of Generative_AI_Service with the same slug, but instead it returned another slug %2$s.', 'ai-services' ),
						$this->slug,
						$instance->get_service_slug()
					)
				)
			);
		}

		// Wrap the instance in a decorator for centralized functionality.
		return new AI_Service_Decorator( $instance );
	}

	/**
	 * Gets the service name.
	 *
	 * @since 0.1.0
	 *
	 * @return string The service name.
	 */
	public function get_name(): string {
		return $this->args['name'];
	}

	/**
	 * Gets the service credentials URL, if specified.
	 *
	 * @since 0.1.0
	 *
	 * @return string The service credentials URL, or empty string if not specified.
	 */
	public function get_credentials_url(): string {
		return $this->args['credentials_url'];
	}

	/**
	 * Checks whether the service can be overridden.
	 *
	 * @since 0.1.0
	 *
	 * @return bool True if the service can be overridden, false otherwise.
	 */
	public function allows_override(): bool {
		return $this->args['allow_override'];
	}

	/**
	 * Validates the service slug.
	 *
	 * @since 0.1.0
	 *
	 * @param string $slug The service slug.
	 *
	 * @throws InvalidArgumentException Thrown if the service slug contains disallowed characters.
	 */
	private function validate_slug( string $slug ): void {
		if ( ! preg_match( '/^[a-z0-9-]+$/', $slug ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'The service slug must only contain lowercase letters, numbers, and hyphens.', 'ai-services' )
			);
		}
	}

	/**
	 * Parses the service registration arguments.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $args The service registration arguments.
	 * @return array<string, mixed> The parsed service registration arguments.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid argument is provided.
	 */
	private function parse_args( array $args ): array {
		if ( isset( $args['name'] ) ) {
			$args['name'] = (string) $args['name'];
		} else {
			$args['name'] = ucwords( str_replace( array( '-', '_' ), ' ', $this->slug ) );
		}

		if ( isset( $args['credentials_url'] ) ) {
			$args['credentials_url'] = sanitize_url( (string) $args['credentials_url'] );
		} else {
			$args['credentials_url'] = '';
		}

		if ( isset( $args['allow_override'] ) ) {
			$args['allow_override'] = (bool) $args['allow_override'];
		} else {
			$args['allow_override'] = true;
		}

		return $this->parse_instance_args( $args );
	}

	/**
	 * Parses the service registration instance arguments.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $args The service registration instance arguments.
	 * @return array<string, mixed> The parsed service registration instance arguments.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid instance argument is provided.
	 */
	private function parse_instance_args( array $args ): array {
		$requirements_map = array(
			'request_handler' => array( Request_Handler::class, HTTP_With_Streams::class ),
			'container'       => array( Container::class, Option_Container::class ),
			'repository'      => array( Key_Value_Repository::class, Option_Repository::class ),
		);

		foreach ( $requirements_map as $key => $requirements ) {
			list( $interface_name, $class_name ) = $requirements;

			if ( isset( $args[ $key ] ) ) {
				if ( ! $args[ $key ] instanceof $interface_name ) {
					throw new InvalidArgumentException(
						esc_html(
							sprintf(
								/* translators: 1: argument name, 2: class name */
								__( 'The %1$s argument must be an instance of %2$s.', 'ai-services' ),
								$key,
								$interface_name
							)
						)
					);
				}
			} else {
				$args[ $key ] = new $class_name();
			}
		}

		return $args;
	}
}
