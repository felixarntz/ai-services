<?php
/**
 * Uninstall script for the plugin.
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

// This loader file should remain compatible with PHP 5.2.

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit; // Prevent execution from directly accessing the file.
}

require_once plugin_dir_path( __FILE__ ) . 'constants.php';

if ( ! function_exists( 'wp_starter_plugin_uninstall' ) ) {

	/**
	 * Checks basic requirements and uninstalls the plugin.
	 *
	 * @since n.e.x.t
	 */
	function wp_starter_plugin_uninstall() /* @phpstan-ignore-line */ {
		$plugin_main_file = plugin_dir_path( __FILE__ ) . 'load.php';

		// Check for supported PHP version.
		if (
			version_compare( phpversion(), WP_STARTER_PLUGIN_MINIMUM_PHP, '<' )
			|| version_compare( get_bloginfo( 'version' ), WP_STARTER_PLUGIN_MINIMUM_WP, '<' )
		) {
			return;
		}

		// Register the autoloader.
		if ( ! wp_starter_plugin_register_autoloader() ) {
			return;
		}

		// Assemble the plugin services.
		$class_name = 'Vendor_NS\WP_Starter_Plugin\Plugin_Service_Container_Builder';
		$builder    = new $class_name();
		$services   = $builder->build_env( $plugin_main_file )
			->build_services()
			->get();

		// Run the plugin data uninstaller.
		$services['plugin_installer']->uninstall();
	}
}

wp_starter_plugin_uninstall();
