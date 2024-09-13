<?php
/**
 * Tests for Felix_Arntz\AI_Services\Plugin_Main
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\PHPUnit\Tests;

use Felix_Arntz\AI_Services\PHPUnit\Includes\Test_Case;
use Felix_Arntz\AI_Services\Plugin_Main;
use Felix_Arntz\AI_Services\Services\Services_API_Instance;
use ReflectionProperty;

class Plugin_Main_Tests extends Test_Case {

	private $plugin_main;

	public function set_up() {
		parent::set_up();

		// Clear up the singleton-like instance to prevent error from trying to set it again by via Plugin_Main.
		$prop = new ReflectionProperty( Services_API_Instance::class, 'instance' );
		$prop->setAccessible( true );
		$prop->setValue( null, null );
		$prop->setAccessible( false );

		$this->plugin_main = new Plugin_Main( TESTS_PLUGIN_DIR . '/load.php' );
	}

	public function test_add_hooks() {
		$actions_to_check = array(
			// Via Plugin_Main.
			'init',
			'activate_' . basename( TESTS_PLUGIN_DIR ) . '/load.php',

			// Via Services_Loader.
			'plugins_loaded',
			'admin_menu',
		);
		foreach ( $actions_to_check as $hook_name ) {
			remove_all_actions( $hook_name );
		}

		$this->plugin_main->add_hooks();

		foreach ( $actions_to_check as $hook_name ) {
			$this->assertHasAction( $hook_name );
		}
	}
}
