<?php
/**
 * Plugin Name: Write Excerpt Plugin
 * Plugin URI: https://felixarntz.github.io/ai-services/
 * Description: Example plugin using AI Services to implement a command to write a post excerpt.
 * Requires at least: 6.0
 * Requires PHP: 7.2
 * Version: 1.0.0
 * Author: Felix Arntz
 * Author URI: https://felix-arntz.me
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: write-excerpt-plugin
 * Requires Plugins: ai-services
 */

add_action(
	'enqueue_block_editor_assets',
	static function () {
		if ( ! function_exists( 'ai_services' ) ) {
			return;
		}

		wp_enqueue_script(
			'write-excerpt-plugin',
			plugin_dir_url( __FILE__ ) . 'index.js',
			array(
				'wp-commands',
				'wp-components',
				'wp-data',
				'wp-element',
				'wp-block-editor',
				'wp-editor',
				'ais-ai',
				'wp-blocks',
				'wp-plugins',
				'wp-i18n',
			),
			'1.0.0',
			array( 'strategy' => 'defer' )
		);
	}
);
