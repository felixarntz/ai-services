<?php
/**
 * Class Felix_Arntz\AI_Services\Plugin_Main
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services;

use Felix_Arntz\AI_Services\Anthropic\Anthropic_AI_Service;
use Felix_Arntz\AI_Services\Anthropic\Anthropic_AI_Text_Generation_Model;
use Felix_Arntz\AI_Services\Google\Google_AI_Image_Generation_Model;
use Felix_Arntz\AI_Services\Google\Google_AI_Service;
use Felix_Arntz\AI_Services\Google\Google_AI_Text_Generation_Model;
use Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Image_Generation_Model;
use Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Service;
use Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Text_Generation_Model;
use Felix_Arntz\AI_Services\Services\API\Enums\Service_Type;
use Felix_Arntz\AI_Services\Services\Service_Registration_Context;
use Felix_Arntz\AI_Services\Services\Services_API;
use Felix_Arntz\AI_Services\Services\Services_API_Instance;
use Felix_Arntz\AI_Services\Services\Services_Loader;
use Felix_Arntz\AI_Services\Services\Util\AI_Capabilities;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\With_Hooks;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Service_Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Hook_Registrar;

/**
 * Plugin main class.
 *
 * @since 0.1.0
 */
class Plugin_Main implements With_Hooks {

	/**
	 * Plugin service container.
	 *
	 * @since 0.1.0
	 * @var Service_Container
	 */
	private $container;

	/**
	 * Services loader.
	 *
	 * @since 0.1.0
	 * @var Services_Loader
	 */
	private $services_loader;

	/**
	 * Services API instance.
	 *
	 * @since 0.1.0
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 */
	public function __construct( string $main_file ) {
		// Instantiate the services loader, which separately initializes all functionality related to the AI services.
		$this->services_loader = new Services_Loader( $main_file );

		// Then retrieve the canonical AI services instance, which is created by the services loader.
		$this->services_api = Services_API_Instance::get();

		// Last but not least, set up the container for the main plugin functionality.
		$this->container = $this->set_up_container( $main_file );

		$this->register_default_services();
	}

	/**
	 * Adds relevant WordPress hooks.
	 *
	 * @since 0.1.0
	 */
	public function add_hooks(): void {
		$this->services_loader->add_hooks();
		$this->maybe_install_data();
		$this->add_cleanup_hooks();
		$this->add_service_hooks();
	}

	/**
	 * Listens to the 'init' action and plugin activation to conditionally trigger the installation process.
	 *
	 * The installation will only happen if necessary, i.e. on most requests this will effectively do nothing.
	 *
	 * @since 0.1.0
	 */
	private function maybe_install_data(): void {
		/*
		 * Run plugin data installation/upgrade logic early on 'init' if necessary.
		 * This is primarily used to run upgrade routines as necessary.
		 * However, for network-wide plugin activation on a multisite this is also used to install the plugin data.
		 * While intuitively the latter may fit better into the plugin activation hook, that approach has problems on
		 * larger multisite installations.
		 * The plugin installer class will ensure that the installation only runs if necessary.
		 */
		add_action(
			'init',
			function () {
				if ( ! $this->container['current_user']->has_cap( 'activate_plugins' ) ) {
					return;
				}
				$this->container['plugin_installer']->install();
			},
			0
		);

		/*
		 * Plugin activation hook. This is only used to install the plugin data for a single site.
		 * If activated for a multisite network, the plugin data is instead installed on 'init', per individual site,
		 * since handling it all within the activation hook is not scalable.
		 */
		register_activation_hook(
			$this->container['plugin_env']->main_file(),
			function ( $network_wide ) {
				if ( $network_wide ) {
					return;
				}
				$this->container['plugin_installer']->install();
			}
		);
	}

	/**
	 * Adds cleanup hooks related to plugin deactivation.
	 *
	 * @since 0.1.0
	 */
	private function add_cleanup_hooks(): void {
		// This function is only available in WordPress 6.4+.
		if ( ! function_exists( 'wp_set_options_autoload' ) ) {
			return;
		}

		// Disable autoloading of plugin options on deactivation.
		register_deactivation_hook(
			$this->container['plugin_env']->main_file(),
			function ( $network_wide ) {
				// For network-wide deactivation, this cleanup cannot be reliably implemented.
				if ( $network_wide ) {
					return;
				}

				$autoloaded_options = $this->get_autoloaded_options();
				if ( ! $autoloaded_options ) {
					return;
				}

				wp_set_options_autoload(
					$autoloaded_options,
					false
				);
			}
		);

		// Reinstate original autoload settings on (re-)activation.
		register_activation_hook(
			$this->container['plugin_env']->main_file(),
			function ( $network_wide ) {
				// See deactivation hook for network-wide cleanup limitations.
				if ( $network_wide ) {
					return;
				}

				$autoloaded_options = $this->get_autoloaded_options();
				if ( ! $autoloaded_options ) {
					return;
				}

				wp_set_options_autoload(
					$autoloaded_options,
					true
				);
			}
		);
	}

	/**
	 * Adds general service hooks on 'init' to initialize the plugin.
	 *
	 * @since 0.1.0
	 */
	private function add_service_hooks(): void {
		// Register options.
		$option_registrar = new Option_Hook_Registrar( $this->container['option_registry'] );
		$option_registrar->add_register_callback(
			function ( $registry ) {
				foreach ( $this->container['option_container']->get_keys() as $key ) {
					$option = $this->container['option_container']->get( $key );
					$registry->register(
						$option->get_key(),
						$option->get_registration_args()
					);
				}
			}
		);

		// Load chatbot if needed.
		add_action(
			'init',
			function () {
				if ( $this->container['chatbot_loader']->can_load() ) {
					$this->container['chatbot_loader']->load( $this->container['chatbot'] );
				}
			}
		);
	}

	/**
	 * Gets the plugin option names that are autoloaded.
	 *
	 * @since 0.1.0
	 *
	 * @return string[] List of autoloaded plugin options.
	 */
	private function get_autoloaded_options(): array {
		$autoloaded_options = array();

		foreach ( $this->container['option_container']->get_keys() as $key ) {
			// Trigger option instantiation so that the autoload config is populated.
			$this->container['option_container']->get( $key );

			$autoload = $this->container['option_repository']->get_autoload_config( $key );

			if ( true === $autoload ) {
				$autoloaded_options[] = $key;
			}
		}

		return $autoloaded_options;
	}

	/**
	 * Sets up the plugin container.
	 *
	 * @since 0.1.0
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 * @return Service_Container Plugin container.
	 */
	private function set_up_container( string $main_file ): Service_Container {
		$builder = new Plugin_Service_Container_Builder();

		return $builder->build_env( $main_file )
			->build_services()
			->get();
	}

	/**
	 * Registers the default AI services.
	 *
	 * @since 0.1.0
	 */
	private function register_default_services(): void {
		$this->services_api->register_service(
			'anthropic',
			static function ( Service_Registration_Context $context ) {
				return new Anthropic_AI_Service(
					$context->get_metadata(),
					$context->get_authentication(),
					$context->get_request_handler()
				);
			},
			array(
				'name'            => 'Anthropic (Claude)',
				'credentials_url' => 'https://console.anthropic.com/settings/keys',
				'type'            => Service_Type::CLOUD,
				'capabilities'    => AI_Capabilities::get_model_classes_capabilities(
					array( Anthropic_AI_Text_Generation_Model::class )
				),
				'allow_override'  => false,
			)
		);
		$this->services_api->register_service(
			'google',
			static function ( Service_Registration_Context $context ) {
				return new Google_AI_Service(
					$context->get_metadata(),
					$context->get_authentication(),
					$context->get_request_handler()
				);
			},
			array(
				'name'            => 'Google (Gemini, Imagen)',
				'credentials_url' => 'https://aistudio.google.com/app/apikey',
				'type'            => Service_Type::CLOUD,
				'capabilities'    => AI_Capabilities::get_model_classes_capabilities(
					array(
						Google_AI_Text_Generation_Model::class,
						Google_AI_Image_Generation_Model::class,
					)
				),
				'allow_override'  => false,
			)
		);
		$this->services_api->register_service(
			'openai',
			static function ( Service_Registration_Context $context ) {
				return new OpenAI_AI_Service(
					$context->get_metadata(),
					$context->get_authentication(),
					$context->get_request_handler()
				);
			},
			array(
				'name'            => 'OpenAI (GPT, Dall-E)',
				'credentials_url' => 'https://platform.openai.com/api-keys',
				'type'            => Service_Type::CLOUD,
				'capabilities'    => AI_Capabilities::get_model_classes_capabilities(
					array(
						OpenAI_AI_Text_Generation_Model::class,
						OpenAI_AI_Image_Generation_Model::class,
					)
				),
				'allow_override'  => false,
			)
		);
	}
}
