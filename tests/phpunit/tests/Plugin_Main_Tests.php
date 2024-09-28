<?php
/**
 * Tests for Vendor_NS\WP_Starter_Plugin\Plugin_Main
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\PHPUnit\Tests;

use Vendor_NS\WP_Starter_Plugin\PHPUnit\Includes\Test_Case;
use Vendor_NS\WP_Starter_Plugin\Plugin_Main;

class Plugin_Main_Tests extends Test_Case {

	private $plugin_main;

	public function set_up() {
		parent::set_up();
		$this->plugin_main = new Plugin_Main( TESTS_PLUGIN_DIR . '/wp-starter-plugin.php' );
	}

	public function test_add_hooks() {
		$actions_to_check = array(
			'init',
			'activate_' . basename( TESTS_PLUGIN_DIR ) . '/wp-starter-plugin.php',
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
