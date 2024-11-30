<?php
/**
 * Class Felix_Arntz\AI_Services\PHPUnit\Includes\Test_Case
 *
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\PHPUnit\Includes;

use ReflectionMethod;
use ReflectionProperty;
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

	/**
	 * Gets the value for an inaccessible class property, temporarily making it accessible.
	 *
	 * @param object|string $instance_or_class Instance or class name.
	 * @param string        $property_name     Property name.
	 * @return mixed Value of the property.
	 */
	protected function getInaccessibleProperty( $instance_or_class, string $property_name ) {
		$prop = new ReflectionProperty( $instance_or_class, $property_name );
		$prop->setAccessible( true );

		if ( is_object( $instance_or_class ) ) {
			// Non-static property.
			$value = $prop->getValue( $instance_or_class );
		} else {
			// Static property.
			$value = $prop->getValue( null );
		}

		$prop->setAccessible( false );

		return $value;
	}

	/**
	 * Sets the value for an inaccessible class property, temporarily making it accessible.
	 *
	 * @param object|string $instance_or_class Instance or class name.
	 * @param string        $property_name     Property name.
	 * @param mixed         $value             Value to set the property to.
	 */
	protected function setInaccessibleProperty( $instance_or_class, string $property_name, $value ): void {
		$prop = new ReflectionProperty( $instance_or_class, $property_name );
		$prop->setAccessible( true );

		if ( is_object( $instance_or_class ) ) {
			// Non-static property.
			$prop->setValue( $instance_or_class, $value );
		} else {
			// Static property.
			$prop->setValue( null, $value );
		}

		$prop->setAccessible( false );
	}

	/**
	 * Calls an inaccessible class method, temporarily making it accessible.
	 *
	 * @param object|string $instance_or_class Instance or class name.
	 * @param string        $method_name       Method name to call.
	 * @param mixed[]       ...$args           Optional. Arguments to pass to the method.
	 * @return mixed Return value from the method.
	 */
	protected function callInaccessibleMethod( $instance_or_class, string $method_name, ...$args ) {
		$method = new ReflectionMethod( $instance_or_class, $method_name );
		$method->setAccessible( true );

		if ( is_object( $instance_or_class ) ) {
			// Non-static method.
			$result = $method->invoke( $instance_or_class, ...$args );
		} else {
			// Static method.
			$result = $method->invoke( null, ...$args );
		}

		$method->setAccessible( false );

		return $result;
	}

	/**
	 * Creates a basic mock instance for the given class name.
	 *
	 * @param string $class_name Class name to create a mock for.
	 * @return object Mock instance.
	 */
	protected function createBasicMock( string $class_name ): object {
		return $this->getMockBuilder( $class_name )
			->disableOriginalConstructor()
			->getMock();
	}
}
