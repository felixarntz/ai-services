<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Plugin_Main
 *
 * @since n.e.x.t
 * @package wp-oop-plugin-lib-example
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example;

use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Services_API;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Services_API_Instance;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Services_Loader;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\With_Hooks;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Service_Container;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Hook_Registrar;

/**
 * Plugin main class.
 *
 * @since n.e.x.t
 */
class Plugin_Main implements With_Hooks {

	/**
	 * Plugin service container.
	 *
	 * @since n.e.x.t
	 * @var Service_Container
	 */
	private $container;

	/**
	 * Services loader.
	 *
	 * @since n.e.x.t
	 * @var Services_Loader
	 */
	private $services_loader;

	/**
	 * Services API instance.
	 *
	 * @since n.e.x.t
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 */
	public function __construct( string $main_file ) {
		$plugin_env = new Plugin_Env( $main_file, WP_OOP_PLUGIN_LIB_EXAMPLE_VERSION );

		// Instantiate the services loader, which separately initializes all functionality related to the AI services.
		$this->services_loader = new Services_Loader( $plugin_env );

		// Then retrieve the canonical AI services instance, which is created by the services loader.
		$this->services_api = Services_API_Instance::get();

		// Last but not least, set up the container for the main plugin functionality.
		$this->container = $this->set_up_container( $plugin_env );

		// TODO: Remove this once the services API is fully integrated (it's only here to please PHPStan).
		$this->services_api->get_registered_service_slugs();
	}

	/**
	 * Adds relevant WordPress hooks.
	 *
	 * @since n.e.x.t
	 */
	public function add_hooks(): void {
		$this->services_loader->add_hooks();
		$this->maybe_install_data();
		$this->add_service_hooks();

		// Testing.
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-info"><p>';

				$model = $this->container['chatbot_ai']->get_model();
				try {
					$candidates = $model->generate_content( 'Where can I add new pages?' );
					$text       = $this->container['chatbot_ai']->get_text_from_candidates( $candidates );
					var_dump( $text ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
				} catch ( \Exception $e ) {
					echo 'An error occurred: ';
					echo esc_html( $e->getMessage() );
				}
				echo '</p></div>';
			}
		);
	}

	/**
	 * Listens to the 'init' action and plugin activation to conditionally trigger the installation process.
	 *
	 * The installation will only happen if necessary, i.e. on most requests this will effectively do nothing.
	 *
	 * @since n.e.x.t
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
	 * Adds general service hooks on 'init' to initialize the plugin.
	 *
	 * @since n.e.x.t
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
	 * Sets up the plugin service container.
	 *
	 * @since n.e.x.t
	 *
	 * @param Plugin_Env $plugin_env Plugin environment object.
	 * @return Service_Container The plugin service container.
	 */
	private function set_up_container( Plugin_Env $plugin_env ): Service_Container {
		$builder = new Plugin_Service_Container_Builder();

		return $builder->set_env( $plugin_env )
			->build_services()
			->get();
	}
}
