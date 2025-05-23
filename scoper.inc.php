<?php
/**
 * PHP-Scoper configuration file.
 *
 * @package ai-services
 */

use Symfony\Component\Finder\Finder;

/*
 * Specify the text domain for this plugin.
 * It will be used in all translation strings from the underlying library, to make sure they are properly translatable
 * as part of the plugin.
 */
$plugin_textdomain = 'ai-services';

/*
 * You can optionally provide specific folder names from the WP OOP Plugin Lib "src" directory here to limit the
 * library classes in your project to only those. If you only need a few classes from the library in your project, this
 * helps keep the PHP footprint and the project's file size small.
 */
$wp_oop_plugin_lib_folders = array(
	'Admin_Links',
	'Admin_Pages',
	'Admin_Pointers',
	'Capabilities',
	'Dependencies',
	'Entities',
	'General',
	'HTTP',
	'Installation',
	'Meta',
	'Options',
	'REST_Routes',
	'Validation',
);

$wp_oop_plugin_lib_folders_concat = implode( '|', $wp_oop_plugin_lib_folders );

$wp_oop_plugin_lib_folders_regex = $wp_oop_plugin_lib_folders_concat
	? "/^($wp_oop_plugin_lib_folders_concat)\//"
	: '/^[A-Za-z0-9_]\//';

$other_dependencies_regex = '/^(guzzlehttp|psr)\/[a-z0-9-]+\/src\//';

return array(
	'prefix'             => 'Felix_Arntz\AI_Services_Dependencies',
	'finders'            => array(
		Finder::create()
			->files()
			->ignoreVCS( true )
			->notName( '/LICENSE|.*\\.md|.*\\.json|.*\\.lock|.*\\.dist/' )
			->exclude( array( 'docs', 'tests' ) )
			->path( $wp_oop_plugin_lib_folders_regex )
			->in( 'vendor/felixarntz/wp-oop-plugin-lib/src' ),
		Finder::create()
			->files()
			->ignoreVCS( true )
			->notName( '/LICENSE|.*\\.md|.*\\.json|.*\\.lock|.*\\.dist/' )
			->exclude( array( 'docs', 'tests' ) )
			->path( $other_dependencies_regex )
			->in( 'vendor' ),
	),
	'patchers'           => array(
		// Patcher to replace the library text domain with the plugin text domain.
		function ( $file_path, $prefix, $content ) use ( $plugin_textdomain ) {
			if ( $plugin_textdomain ) {
				return str_replace(
					array(
						"'wp-oop-plugin-lib' )",
						"'wp-oop-plugin-lib')",
					),
					array(
						"'" . $plugin_textdomain . "' )",
						"'" . $plugin_textdomain . "')",
					),
					$content
				);
			}
			return $content;
		},
	),
	'exclude-namespaces' => array( 'WpOrg' ),
	'exclude-classes'    => array( '/^(WP|Requests)_[A-Za-z0-9_]+$/' ),
);
