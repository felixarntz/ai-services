<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\PHPUnit\Includes\Test_Case
 *
 * @package wp-oop-plugin-lib-example
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\PHPUnit\Includes;

use WP_UnitTestCase;

/**
 * Basic class for unit tests of the plugin.
 */
abstract class Test_Case extends WP_UnitTestCase {

	/**
	 * Asserts that the given hook has one or more actions added.
	 *
	 * @param string $hook_name Action hook name.
	 * @param string $message   Optional. Message to display when the assertion fails.
	 */
	public function assertHasAction( $hook_name, $message = '' ) {
		if ( ! $message ) {
			$message = sprintf( 'Failed asserting that any action is added to the %s hook.', $hook_name );
		}
		$this->assertTrue( has_action( $hook_name ), $message );
	}

	/**
	 * Asserts that the given hook has no actions added.
	 *
	 * @param string $hook_name Action hook name.
	 * @param string $message   Optional. Message to display when the assertion fails.
	 */
	public function assertNotHasAction( $hook_name, $message = '' ) {
		if ( ! $message ) {
			$message = sprintf( 'Failed asserting that no action is added to the %s hook.', $hook_name );
		}
		$this->assertFalse( has_action( $hook_name ), $message );
	}

	/**
	 * Asserts that the given hook has one or more filters added.
	 *
	 * @param string $hook_name Filter hook name.
	 * @param string $message   Optional. Message to display when the assertion fails.
	 */
	public function assertHasFilter( $hook_name, $message = '' ) {
		if ( ! $message ) {
			$message = sprintf( 'Failed asserting that any filter is added to the %s hook.', $hook_name );
		}
		$this->assertTrue( has_filter( $hook_name ), $message );
	}

	/**
	 * Asserts that the given hook has no filters added.
	 *
	 * @param string $hook_name Filter hook name.
	 * @param string $message   Optional. Message to display when the assertion fails.
	 */
	public function assertNotHasFilter( $hook_name, $message = '' ) {
		if ( ! $message ) {
			$message = sprintf( 'Failed asserting that no filter is added to the %s hook.', $hook_name );
		}
		$this->assertFalse( has_filter( $hook_name ), $message );
	}
}
