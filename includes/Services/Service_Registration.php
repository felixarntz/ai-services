<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Service_Registration
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Felix_Arntz\AI_Services\Services;

use InvalidArgumentException;
use RuntimeException;
use Felix_Arntz\AI_Services\Services\Cache\Cached_AI_Service;
use Felix_Arntz\AI_Services\Services\Cache\Cached_AI_Service_With_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Repository;

/**
 * Class representing a service registration.
 *
 * This is an internal class and NOT the actual service.
 *
 * @since n.e.x.t
 */
final class Service_Registration {

	/**
	 * The service slug.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $slug;

	/**
	 * The service creator.
	 *
	 * @since n.e.x.t
	 * @var callable
	 */
	private $creator;

	/**
	 * The service arguments.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $args;

	/**
	 * The API key option slug.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $api_key_option_slug;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $slug    The service slug. Must only contain lowercase letters, numbers, hyphens.
	 * @param callable             $creator The service creator. Receives the API key (string) as first parameter, the
	 *                                      HTTP instance as second parameter, and must return a Generative_AI_Service
	 *                                      instance. Optionally, the class can implement the With_API_Client
	 *                                      interface, if the service uses an API client class. Doing so benefits
	 *                                      performance, as it allows the infrastructure to perform batch requests
	 *                                      across multiple services.
	 * @param array<string, mixed> $args    {
	 *     Optional. The service arguments. Default empty array.
	 *
	 *     @type string            $name              The service name. Default is the slug with spaces and uppercase
	 *                                                first letters.
	 *     @type bool              $allow_override    Whether the service can be overridden by another service with the
	 *                                                same slug. Default true.
	 *     @type Option_Container  $option_container  The option container instance. Default is a new instance.
	 *     @type Option_Repository $option_repository The option repository instance. Default is a new instance.
	 *     @type HTTP              $http              The HTTP instance. Default is a new instance.
	 * }
	 */
	public function __construct( string $slug, callable $creator, array $args = array() ) {
		$this->validate_slug( $slug );

		$this->slug    = $slug;
		$this->creator = $creator;
		$this->args    = $this->parse_args( $args );

		$this->api_key_option_slug                                    = sprintf( 'ais_%s_api_key', $slug );
		$this->args['option_container'][ $this->api_key_option_slug ] = function () {
			return new Option(
				$this->args['option_repository'],
				$this->api_key_option_slug,
				array(
					'type'         => 'string',
					'default'      => '',
					'show_in_rest' => true,
					'autoload'     => true,
				)
			);
		};
	}

	/**
	 * Gets the service slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Gets the API key option.
	 *
	 * @since n.e.x.t
	 *
	 * @return Option The API key option.
	 */
	public function get_api_key_option(): Option {
		return $this->args['option_container'][ $this->api_key_option_slug ];
	}

	/**
	 * Gets the API key option slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The API key option slug.
	 */
	public function get_api_key_option_slug(): string {
		return $this->api_key_option_slug;
	}

	/**
	 * Creates a new instance of the service.
	 *
	 * @since n.e.x.t
	 *
	 * @return Generative_AI_Service The service instance.
	 *
	 * @throws RuntimeException Thrown if no API key is set for the service or if the service creator does not return a
	 *                          Generative_AI_Service instance.
	 */
	public function create_instance(): Generative_AI_Service {
		$api_key = $this->get_api_key_option()->get_value();

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

		$instance = ( $this->creator )( $api_key, $this->args['http'] );
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

		// Wrap the instance in a cache decorator.
		if ( $instance instanceof With_API_Client ) {
			$instance = new Cached_AI_Service_With_API_Client( $instance );
		} else {
			$instance = new Cached_AI_Service( $instance );
		}

		return $instance;
	}

	/**
	 * Gets the service name.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The service name.
	 */
	public function get_name(): string {
		return $this->args['name'];
	}

	/**
	 * Checks whether the service can be overridden.
	 *
	 * @since n.e.x.t
	 *
	 * @return bool True if the service can be overridden, false otherwise.
	 */
	public function allows_override(): bool {
		return $this->args['allow_override'];
	}

	/**
	 * Validates the service slug.
	 *
	 * @since n.e.x.t
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
	 * @since n.e.x.t
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

		if ( isset( $args['allow_override'] ) ) {
			$args['allow_override'] = (bool) $args['allow_override'];
		} else {
			$args['allow_override'] = true;
		}

		if ( isset( $args['option_container'] ) ) {
			if ( ! $args['option_container'] instanceof Option_Container ) {
				throw new InvalidArgumentException(
					esc_html__( 'The option_container argument must be an instance of Option_Container.', 'ai-services' )
				);
			}
		} else {
			$args['option_container'] = new Option_Container();
		}

		if ( isset( $args['option_repository'] ) ) {
			if ( ! $args['option_repository'] instanceof Option_Repository ) {
				throw new InvalidArgumentException(
					esc_html__( 'The option_repository argument must be an instance of Option_Repository.', 'ai-services' )
				);
			}
		} else {
			$args['option_repository'] = new Option_Repository();
		}

		if ( isset( $args['http'] ) ) {
			if ( ! $args['http'] instanceof HTTP ) {
				throw new InvalidArgumentException(
					esc_html__( 'The http argument must be an instance of HTTP.', 'ai-services' )
				);
			}
		} else {
			$args['http'] = new HTTP();
		}

		return $args;
	}
}
