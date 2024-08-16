<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\Services_API
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Services;

use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service;
use Vendor_NS\WP_Starter_Plugin\Services\Contracts\With_API_Client;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Options\Option_Encrypter;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\HTTP\HTTP;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Repository;

/**
 * Main API class providing the entry point to the generative AI services.
 *
 * @since n.e.x.t
 */
final class Services_API {

	/**
	 * The service registration definitions, keyed by service slug.
	 *
	 * @since n.e.x.t
	 * @var array<string, Service_Registration>
	 */
	private $service_registrations = array();

	/**
	 * The service instances, keyed by service slug.
	 *
	 * @since n.e.x.t
	 * @var array<string, Generative_AI_Service>
	 */
	private $service_instances = array();

	/**
	 * The current user instance.
	 *
	 * @since n.e.x.t
	 * @var Current_User
	 */
	private $current_user;

	/**
	 * The option container instance.
	 *
	 * @since n.e.x.t
	 * @var Option_Container
	 */
	private $option_container;

	/**
	 * The option repository instance.
	 *
	 * @since n.e.x.t
	 * @var Option_Repository
	 */
	private $option_repository;

	/**
	 * The option encrypter instance.
	 *
	 * @since n.e.x.t
	 * @var Option_Encrypter
	 */
	private $option_encrypter;

	/**
	 * The HTTP instance.
	 *
	 * @since n.e.x.t
	 * @var HTTP
	 */
	private $http;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Current_User      $current_user      The current user instance.
	 * @param Option_Container  $option_container  The option container instance.
	 * @param Option_Repository $option_repository The option repository instance.
	 * @param Option_Encrypter  $option_encrypter  The option encrypter instance.
	 * @param HTTP              $http              The HTTP instance.
	 */
	public function __construct(
		Current_User $current_user,
		Option_Container $option_container,
		Option_Repository $option_repository,
		Option_Encrypter $option_encrypter,
		HTTP $http
	) {
		$this->current_user      = $current_user;
		$this->option_container  = $option_container;
		$this->option_repository = $option_repository;
		$this->option_encrypter  = $option_encrypter;
		$this->http              = $http;
	}

	/**
	 * Registers a generative AI service.
	 *
	 * @since n.e.x.t
	 *
	 * @see Generative_AI_Service
	 * @see With_API_Client
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
	 *     @type string $name           The service name. Default is the slug with spaces and uppercase first letters.
	 *     @type bool   $allow_override Whether the service can be overridden by another service with the same slug.
	 *                                  Default true.
	 * }
	 *
	 * @throws InvalidArgumentException Thrown if an already registered slug or invalid arguments are provided.
	 */
	public function register_service( string $slug, callable $creator, array $args = array() ): void {
		if ( isset( $this->service_registrations[ $slug ] ) && ! $this->service_registrations[ $slug ]->allows_override() ) {
			throw new InvalidArgumentException(
				esc_html(
					sprintf(
						/* translators: %s: The service slug. */
						esc_html__( 'Service %s is already registered and cannot be overridden.', 'wp-starter-plugin' ),
						$slug
					)
				)
			);
		}

		$args['option_container']  = $this->option_container;
		$args['option_repository'] = $this->option_repository;
		$args['http']              = $this->http;

		$this->service_registrations[ $slug ] = new Service_Registration( $slug, $creator, $args );

		// Ensure the API key option is encrypted.
		$option_slug = $this->service_registrations[ $slug ]->get_api_key_option_slug();
		if ( ! $this->option_encrypter->has_encryption( $option_slug ) ) {
			$this->option_encrypter->add_encryption_hooks( $option_slug );
		}
	}

	/**
	 * Checks whether a service is registered.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $slug The service slug.
	 * @return bool True if the service is registered, false otherwise.
	 */
	public function is_service_registered( string $slug ): bool {
		return isset( $this->service_registrations[ $slug ] );
	}

	/**
	 * Checks whether a service is available.
	 *
	 * For a service to be considered available, all of the following conditions must be met:
	 * - The service is registered.
	 * - The service has an API key set.
	 * - The API key is valid.
	 * - The current user has the necessary capabilities.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $slug The service slug.
	 * @return bool True if the service is available, false otherwise.
	 */
	public function is_service_available( string $slug ): bool {
		/*
		 * If the service was already instantiated in the class, it is available.
		 * In that case, the only thing left to check is whether the current user has the necessary capabilities.
		 */
		if ( isset( $this->service_instances[ $slug ] ) ) {
			if ( ! $this->current_user->has_cap( 'wpsp_access_service', $slug ) ) {
				return false;
			}
			return true;
		}

		// If the service is not registered, it is not available.
		if ( ! isset( $this->service_registrations[ $slug ] ) ) {
			return false;
		}

		// If no API key is set for the service, it is not available.
		$api_key = $this->service_registrations[ $slug ]->get_api_key_option()->get_value();
		if ( ! $api_key ) {
			return false;
		}

		// Test whether the API key is valid by listing the models.
		$instance = $this->service_registrations[ $slug ]->create_instance();
		try {
			$instance->list_models();
		} catch ( Generative_AI_Exception $e ) {
			return false;
		}

		// If so, the service is available so we can store the instance.
		$this->service_instances[ $slug ] = $instance;

		// Finally, check whether the current user has the necessary capabilities.
		return $this->current_user->has_cap( 'wpsp_access_service', $slug );
	}

	/**
	 * Gets a generative AI service instance.
	 *
	 * Before calling this method, you should check whether the service is available using
	 * {@see Services_API::is_service_available()}.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $slug The service slug.
	 * @return Generative_AI_Service The service instance.
	 *
	 * @throws InvalidArgumentException Thrown if the service is either not registered or not available.
	 */
	public function get_service( string $slug ): Generative_AI_Service {
		if ( ! isset( $this->service_instances[ $slug ] ) ) {
			throw new InvalidArgumentException(
				esc_html(
					sprintf(
						/* translators: %s: The service slug. */
						esc_html__( 'Service %s is either not registered or not available.', 'wp-starter-plugin' ),
						$slug
					)
				)
			);
		}

		return $this->service_instances[ $slug ];
	}

	/**
	 * Gets the list of all registered service slugs.
	 *
	 * @since n.e.x.t
	 *
	 * @return string[] The list of registered service slugs.
	 */
	public function get_registered_service_slugs(): array {
		return array_keys( $this->service_registrations );
	}
}
