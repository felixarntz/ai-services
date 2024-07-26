<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Plugin_Main
 *
 * @since n.e.x.t
 * @package wp-oop-plugin-lib-example
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example;

use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\With_Hooks;
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
	private $services;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 */
	public function __construct( string $main_file ) {
		$this->services = $this->set_up_container( $main_file );
	}

	/**
	 * Adds relevant WordPress hooks.
	 *
	 * @since n.e.x.t
	 */
	public function add_hooks(): void {
		$this->maybe_install_data();
		$this->load_capabilities();
		$this->add_service_hooks();

		// Testing.
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-info"><p>';

				$model = $this->services['chatbot_ai']->get_model();
				try {
					$candidates = $model->generate_content( 'Where can I add new pages?' );
					$text     = $this->services['chatbot_ai']->get_text_from_candidates( $candidates );
					var_dump( $text );
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
				if ( ! $this->services['current_user']->has_cap( 'activate_plugins' ) ) {
					return;
				}
				$this->services['plugin_installer']->install();
			},
			0
		);

		/*
		 * Plugin activation hook. This is only used to install the plugin data for a single site.
		 * If activated for a multisite network, the plugin data is instead installed on 'init', per individual site,
		 * since handling it all within the activation hook is not scalable.
		 */
		register_activation_hook(
			$this->services['plugin_env']->main_file(),
			function ( $network_wide ) {
				if ( $network_wide ) {
					return;
				}
				$this->services['plugin_installer']->install();
			}
		);
	}

	/**
	 * Loads the plugin capabilities and sets up the relevant filters.
	 *
	 * @since n.e.x.t
	 */
	private function load_capabilities() {
		add_action(
			'plugins_loaded',
			function () {
				$controller = $this->services['capability_controller'];

				/**
				 * Fires when the plugin capabilities are loaded.
				 *
				 * @since n.e.x.t
				 *
				 * @param Capability_Controller $controller The capability controller, which can be used to modify the
				 *                                          rules for how capabilities are granted.
				 */
				do_action( 'wpoopple_load_capabilities', $controller );

				$this->services['capability_filters']->add_hooks();
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
		$option_registrar = new Option_Hook_Registrar( $this->services['option_registry'] );
		$option_registrar->add_register_callback(
			function ( $registry ) {
				foreach ( $this->services['option_container']->get_keys() as $key ) {
					$option = $this->services['option_container']->get( $key );
					$registry->register(
						$option->get_key(),
						$option->get_registration_args()
					);
				}
			}
		);

		// Register settings page.
		add_action(
			'admin_menu',
			function () {
				$this->services['admin_settings_menu']->add_page( $this->services['admin_settings_page'] );
			}
		);

		// Load chatbot if needed.
		add_action(
			'init',
			function () {
				if ( $this->services['chatbot_loader']->can_load() ) {
					$this->services['chatbot_loader']->load( $this->services['chatbot'] );
				}
			}
		);
	}

	/**
	 * Sets up the plugin container.
	 *
	 * @since n.e.x.t
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
}
