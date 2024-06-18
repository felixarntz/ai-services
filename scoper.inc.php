<?php
/**
 * PHP-Scoper configuration file.
 *
 * @package wp-oop-plugin-lib-example
 */

use Symfony\Component\Finder\Finder;

/*
 * You can optionally provide specific folder names from the WP OOP Plugin Lib "src" directory here to limit the
 * library classes in your project to only those. If you only need a few classes from the library in your project, this
 * helps keep the PHP footprint and the project's file size small.
 */
$wp_oop_plugin_lib_folders = array(
	'Admin_Pages',
	'Dependencies',
	'Entities',
	'General',
	'Installation',
	'Options',
	'Validation',
);

$wp_oop_plugin_lib_folders_concat = implode( '|', $wp_oop_plugin_lib_folders );

$wp_oop_plugin_lib_folders_regex = $wp_oop_plugin_lib_folders_concat
	? "/^($wp_oop_plugin_lib_folders_concat)/"
	: '/^[A-Za-z0-9_]/';

return array(
	'prefix'          => 'Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies',
	'finders'         => array(
		Finder::create()
			->files()
			->ignoreVCS( true )
			->notName( '/LICENSE|.*\\.md|.*\\.json|.*\\.lock|.*\\.dist/' )
			->exclude( array( 'docs', 'tests' ) )
			->path( $wp_oop_plugin_lib_folders_regex )
			->in( 'vendor/felixarntz/wp-oop-plugin-lib/src' ),
	),
	'exclude-classes' => array( '/^WP_[A-Za-z0-9_]+$/' ),
);
