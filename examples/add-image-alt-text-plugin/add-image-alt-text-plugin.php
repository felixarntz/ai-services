<?php
/**
 * Plugin Name: Add Image Alt Text Plugin
 * Plugin URI: https://wordpress.org/plugins/ai-services/
 * Description: Example plugin using AI Services to implement an image control to generate image alt text.
 * Requires at least: 6.0
 * Requires PHP: 7.2
 * Version: 1.0.0
 * Author: Felix Arntz
 * Author URI: https://felix-arntz.me
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: add-image-alt-text-plugin
 * Requires Plugins: ai-services
 */

add_action(
	'enqueue_block_editor_assets',
	static function () {
		if ( ! function_exists( 'ai_services' ) ) {
			return;
		}

		wp_enqueue_script(
			'add-image-alt-text-plugin',
			plugin_dir_url( __FILE__ ) . 'index.js',
			array(
				'wp-compose',
				'wp-components',
				'wp-data',
				'wp-element',
				'wp-hooks',
				'wp-block-editor',
				'ais-ai',
				'wp-i18n',
			),
			'1.0.0',
			array( 'strategy' => 'defer' )
		);
	}
);
