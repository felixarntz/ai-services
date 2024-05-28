<?php
/**
 * Plugin constants.
 *
 * @since n.e.x.t
 * @package wp-oop-plugin-lib-example
 */

// This loader file should remain compatible with PHP 5.2.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WP_OOP_PLUGIN_LIB_EXAMPLE_VERSION', '1.0.0' );
define( 'WP_OOP_PLUGIN_LIB_EXAMPLE_MINIMUM_PHP', '7.2' );
define( 'WP_OOP_PLUGIN_LIB_EXAMPLE_MINIMUM_WP', '6.0' );

/**
 * Registers the plugin autoloader.
 *
 * @since n.e.x.t
 *
 * @return bool True on success, false on failure.
 */
function wp_oop_plugin_lib_example_register_autoloader() {
	static $registered = null;

	// Prevent multiple executions.
	if ( null !== $registered ) {
		return $registered;
	}

	// Check for the built autoloader class map as that needs to be used for a production build.
	$autoload_file             = plugin_dir_path( __FILE__ ) . 'includes/vendor/composer/autoload_classmap.php';
	$third_party_autoload_file = plugin_dir_path( __FILE__ ) . 'third-party/vendor/composer/autoload_classmap.php';
	if ( file_exists( $autoload_file ) && file_exists( $third_party_autoload_file ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/Plugin_Autoloader.php';

		$class_name = 'Vendor_NS\WP_OOP_Plugin_Lib_Example\Plugin_Autoloader';

		$instance = new $class_name( 'Vendor_NS\WP_OOP_Plugin_Lib_Example', $autoload_file );
		spl_autoload_register( array( $instance, 'autoload' ), true, true );

		$third_party_instance = new $class_name( 'Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies', $third_party_autoload_file );
		spl_autoload_register( array( $third_party_instance, 'autoload' ), true, true );

		$registered = true;
		return true;
	}

	// Otherwise, the autoloader is missing.
	$registered = false;
	return false;
}
