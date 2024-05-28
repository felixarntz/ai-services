<?php
/**
 * Plugin Name: WP OOP Plugin Lib Example
 * Plugin URI: https://the-plugin.com
 * Description: The plugin description.
 * Requires at least: 6.0
 * Requires PHP: 7.2
 * Version: 1.0.0
 * Author: The Plugin Author
 * Author URI: https://the-plugin-author.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: wp-oop-plugin-lib-example
 *
 * @package wp-oop-plugin-lib-example
 */

// This loader file should remain compatible with PHP 5.2.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once plugin_dir_path( __FILE__ ) . 'constants.php';

/**
 * Checks basic requirements and loads the plugin.
 *
 * @since n.e.x.t
 */
function wp_oop_plugin_lib_example_load() /* @phpstan-ignore-line */ {
	static $loaded = false;

	// Check for supported PHP version.
	if (
		version_compare( phpversion(), WP_OOP_PLUGIN_LIB_EXAMPLE_MINIMUM_PHP, '<' )
		|| version_compare( get_bloginfo( 'version' ), WP_OOP_PLUGIN_LIB_EXAMPLE_MINIMUM_WP, '<' )
	) {
		add_action( 'admin_notices', 'wp_oop_plugin_lib_example_display_version_requirements_notice' );
		return;
	}

	// Register the autoloader.
	if ( ! wp_oop_plugin_lib_example_register_autoloader() ) {
		add_action( 'admin_notices', 'wp_oop_plugin_lib_example_display_composer_autoload_notice' );
		return;
	}

	// Prevent loading the plugin twice.
	if ( $loaded ) {
		return;
	}
	$loaded = true;

	// Load the plugin.
	$class_name = 'Vendor_NS\WP_OOP_Plugin_Lib_Example\Plugin_Main';
	$instance   = new $class_name( __FILE__ );
	$instance->add_hooks();
}

/**
 * Displays admin notice about unmet PHP version requirement.
 *
 * @since n.e.x.t
 */
function wp_oop_plugin_lib_example_display_version_requirements_notice() /* @phpstan-ignore-line */ {
	echo '<div class="notice notice-error"><p>';
	echo esc_html(
		sprintf(
			/* translators: 1: required PHP version, 2: required WP version, 3: current PHP version, 4: current WP version */
			__( 'WP OOP Plugin Lib Example requires at least PHP version %1$s and WordPress version %2$s. Your site is currently using PHP %3$s and WordPress %4$s.', 'wp-oop-plugin-lib-example' ),
			WP_OOP_PLUGIN_LIB_EXAMPLE_MINIMUM_PHP,
			phpversion(),
			WP_OOP_PLUGIN_LIB_EXAMPLE_MINIMUM_WP,
			get_bloginfo( 'version' )
		)
	);
	echo '</p></div>';
}

/**
 * Displays admin notice about missing Composer autoload files.
 *
 * @since n.e.x.t
 */
function wp_oop_plugin_lib_example_display_composer_autoload_notice() /* @phpstan-ignore-line */ {
	echo '<div class="notice notice-error"><p>';
	printf(
		/* translators: %s: composer install command */
		esc_html__( 'Your installation of WP OOP Plugin Lib Example is incomplete. Please run %s.', 'wp-oop-plugin-lib-example' ),
		'<code>composer install</code>'
	);
	echo '</p></div>';
}

wp_oop_plugin_lib_example_load();
