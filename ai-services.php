<?php
/**
 * Plugin Name: AI Services
 * Plugin URI: https://felixarntz.github.io/ai-services/
 * Description: Makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.
 * Requires at least: 6.0
 * Requires PHP: 7.2
 * Version: 0.6.2
 * Author: Felix Arntz
 * Author URI: https://felix-arntz.me
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: ai-services
 *
 * @package ai-services
 */

// This loader file should remain compatible with PHP 5.2.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once plugin_dir_path( __FILE__ ) . 'constants.php';

/**
 * Checks basic requirements and loads the plugin.
 *
 * @since 0.1.0
 */
function ai_services_load() /* @phpstan-ignore-line */ {
	static $loaded = false;

	// Check for supported PHP version.
	if (
		version_compare( phpversion(), AI_SERVICES_MINIMUM_PHP, '<' )
		|| version_compare( get_bloginfo( 'version' ), AI_SERVICES_MINIMUM_WP, '<' )
	) {
		add_action( 'admin_notices', 'ai_services_display_version_requirements_notice' );
		return;
	}

	// Register the autoloader.
	if ( ! ai_services_register_autoloader() ) {
		add_action( 'admin_notices', 'ai_services_display_composer_autoload_notice' );
		return;
	}

	// Prevent loading the plugin twice.
	if ( $loaded ) {
		return;
	}
	$loaded = true;

	// Load the plugin.
	$class_name = 'Felix_Arntz\AI_Services\Plugin_Main';
	$instance   = new $class_name( __FILE__ );
	$instance->add_hooks();

	require_once plugin_dir_path( __FILE__ ) . 'includes/api.php';
}

/**
 * Displays admin notice about unmet PHP version requirement.
 *
 * @since 0.1.0
 */
function ai_services_display_version_requirements_notice() /* @phpstan-ignore-line */ {
	echo '<div class="notice notice-error"><p>';
	echo esc_html(
		sprintf(
			/* translators: 1: required PHP version, 2: required WP version, 3: current PHP version, 4: current WP version */
			__( 'AI Services requires at least PHP version %1$s and WordPress version %2$s. Your site is currently using PHP %3$s and WordPress %4$s.', 'ai-services' ),
			AI_SERVICES_MINIMUM_PHP,
			phpversion(),
			AI_SERVICES_MINIMUM_WP,
			get_bloginfo( 'version' )
		)
	);
	echo '</p></div>';
}

/**
 * Displays admin notice about missing Composer autoload files.
 *
 * @since 0.1.0
 */
function ai_services_display_composer_autoload_notice() /* @phpstan-ignore-line */ {
	echo '<div class="notice notice-error"><p>';
	printf(
		/* translators: %s: composer install command */
		esc_html__( 'Your installation of AI Services is incomplete. Please run %s.', 'ai-services' ),
		'<code>composer install</code>'
	);
	echo '</p></div>';
}

ai_services_load();
