<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Service_Registration
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services;

use Felix_Arntz\AI_Services\Services\API\Enums\Service_Type;
use Felix_Arntz\AI_Services\Services\API\Types\Service_Metadata;
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
	 * The service metadata.
	 *
	 * @since n.e.x.t
	 * @var Service_Metadata
	 */
	private $metadata;

	/**
	 * Whether the service can be overridden through another registration with the same slug.
	 *
	 * @since n.e.x.t
	 * @var bool
	 */
	private $allow_override;

	/**
	 * The service creator.
	 *
	 * @since 0.1.0
	 * @var callable
	 */
	private $creator;

	/**
	 * The service instance arguments.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	private $instance_args;

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
	 * @since 0.6.0 The service argument keys were updated.
	 *
	 * @param string               $slug    The service slug. Must only contain lowercase letters, numbers, hyphens.
	 * @param callable             $creator The service creator. Receives the Service_Registration_Context as sole
	 *                                      parameter and must return a Generative_AI_Service instance. The parameter
	 *                                      provides access to the service metadata and other relevant dependencies.
	 * @param array<string, mixed> $args    {
	 *     Optional. The service arguments. Default empty array.
	 *
	 *     @type string               $name            The service name. Default is the slug with spaces and uppercase
	 *                                                 first letters.
	 *     @type string               $credentials_url The URL to manage credentials for the service. Default empty
	 *                                                 string.
	 *     @type string               $type            The service type. Default is Service_Type::CLOUD.
	 *     @type string[]             $capabilities    The list of AI capabilities supported by the service and its
	 *                                                 models. Default empty array.
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
		$this->metadata = Service_Metadata::from_array( array_merge( array( 'slug' => $slug ), $args ) );

		$this->creator        = $creator;
		$this->allow_override = isset( $args['allow_override'] ) ? (bool) $args['allow_override'] : true;
		$this->instance_args  = $this->parse_instance_args( $args );

		$option_definitions = array();
		if ( $this->metadata->get_type() === Service_Type::CLOUD ) {
			$option_definitions = API_Key_Authentication::get_option_definitions( $slug );
		}

		$this->authentication_option_slugs = array();
		foreach ( $option_definitions as $option_slug => $option_args ) {
			$this->authentication_option_slugs[]              = $option_slug;
			$this->instance_args['container'][ $option_slug ] = function () use ( $option_slug, $option_args ) {
				return new Option(
					$this->instance_args['repository'],
					$option_slug,
					$option_args
				);
			};
		}
	}

	/**
	 * Gets the service metadata.
	 *
	 * @since n.e.x.t
	 *
	 * @return Service_Metadata The service metadata.
	 */
	public function get_metadata(): Service_Metadata {
		return $this->metadata;
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
				return $this->instance_args['container'][ $option_slug ];
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
	 * @throws RuntimeException Thrown if no API key is set for the service or if the service creator's return value is
	 *                          not a valid Generative_AI_Service instance.
	 */
	public function create_instance(): Generative_AI_Service {
		$authentication_options = $this->get_authentication_options();

		$slug = $this->metadata->get_slug();

		$authentication = null;
		if ( count( $authentication_options ) > 0 ) {
			// For now an API key is the only authentication method supported.
			$api_key = $authentication_options[0]->get_value();
			if ( ! $api_key ) {
				throw new RuntimeException(
					sprintf(
						'Cannot instantiate service %s without an API key.',
						htmlspecialchars( $slug ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					)
				);
			}
			$authentication = new API_Key_Authentication( $api_key );
		}

		$context = new Service_Registration_Context(
			$slug,
			$this->metadata,
			$this->instance_args['request_handler'],
			$authentication
		);

		$instance = ( $this->creator )( $context );
		if ( ! $instance instanceof Generative_AI_Service ) {
			throw new RuntimeException(
				sprintf(
					'The service creator for %s must return an instance of Generative_AI_Service.',
					htmlspecialchars( $slug ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				)
			);
		}
		if ( $instance->get_service_slug() !== $slug ) {
			throw new RuntimeException(
				sprintf(
					'The service creator for %1$s must return an instance of Generative_AI_Service with the same slug, but instead it returned another slug %2$s.',
					htmlspecialchars( $slug ), // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					htmlspecialchars( $instance->get_service_slug() ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				)
			);
		}
		if ( $instance->get_service_metadata() !== $this->metadata ) {
			throw new RuntimeException(
				sprintf(
					'The service creator for %s must return an instance of Generative_AI_Service with the same metadata, but instead it returned different metadata.',
					htmlspecialchars( $slug ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				)
			);
		}

		// Wrap the instance in a decorator for centralized functionality.
		return new AI_Service_Decorator( $instance );
	}

	/**
	 * Checks whether the service can be overridden.
	 *
	 * @since 0.1.0
	 *
	 * @return bool True if the service can be overridden, false otherwise.
	 */
	public function allows_override(): bool {
		return $this->allow_override;
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

		$instance_args = array();
		foreach ( $requirements_map as $key => $requirements ) {
			list( $interface_name, $class_name ) = $requirements;

			if ( isset( $args[ $key ] ) ) {
				if ( ! $args[ $key ] instanceof $interface_name ) {
					throw new InvalidArgumentException(
						sprintf(
							'The %1$s argument must be an instance of %2$s.',
							htmlspecialchars( $key ), // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
							htmlspecialchars( $interface_name ) // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
						)
					);
				}
				$instance_args[ $key ] = $args[ $key ];
			} else {
				$instance_args[ $key ] = new $class_name();
			}
		}

		return $instance_args;
	}
}
