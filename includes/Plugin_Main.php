<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Plugin_Main
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\With_Hooks;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Service_Container;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Hook_Registrar;

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
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 */
	public function __construct( string $main_file ) {
		$this->container = $this->set_up_container( $main_file );
	}

	/**
	 * Adds relevant WordPress hooks.
	 *
	 * @since n.e.x.t
	 */
	public function add_hooks(): void {
		$this->maybe_install_data();
		$this->add_cleanup_hooks();
		$this->add_service_hooks();

		// Testing.
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-info"><p>';
				if ( $this->container['current_user']->has_cap( 'manage_options' ) ) {
					echo esc_html( $this->container['option_container']['wpsp_version']->get_value() );
					echo '<br>';
					echo esc_html( $this->container['option_container']['wpsp_delete_data']->get_value() );
				} else {
					esc_html_e( 'Current user cannot manage options.', 'wp-starter-plugin' );
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
	 * Adds cleanup hooks related to plugin deactivation.
	 *
	 * @since n.e.x.t
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

		// Register scripts and styles.
		add_action(
			'init',
			function () {
				$this->container['plugin_script_style_loader']->register_scripts_and_styles();
			}
		);

		// Register settings page.
		add_action(
			'admin_menu',
			function () {
				$this->container['admin_settings_menu']->add_page( $this->container['admin_settings_page'] );
			}
		);

		add_action(
			'admin_enqueue_scripts',
			function ( $hook_suffix ) {
				$this->container['admin_pointer_loader']->load_pointers( $hook_suffix );
			}
		);

		add_filter(
			"plugin_action_links_{$this->container['plugin_env']->basename()}",
			function ( array $links ): array {
				return array_merge(
					$this->container['plugin_action_links']->get_tags(),
					$links
				);
			}
		);
	}

	/**
	 * Gets the plugin option names that are autoloaded.
	 *
	 * @since n.e.x.t
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
