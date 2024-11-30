<?php
/**
 * Tests for Felix_Arntz\AI_Services\Services\Services_Loader
 *
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\PHPUnit\Tests;

use Felix_Arntz\AI_Services\PHPUnit\Includes\Test_Case;
use Felix_Arntz\AI_Services\Services\Dependencies\Services_Script_Style_Loader;
use Felix_Arntz\AI_Services\Services\Services_API_Instance;
use Felix_Arntz\AI_Services\Services\Services_Loader;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Admin_Menu;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\REST_Route_Collection;

/**
 * @group services
 */
class Services_Loader_Tests extends Test_Case {

	private $plugin_basename;
	private $services_loader;

	public function set_up() {
		parent::set_up();

		// Clear up the singleton-like instance to prevent error from trying to set it again by via Services_Loader.
		$this->setInaccessibleProperty( Services_API_Instance::class, 'instance', null );

		$this->plugin_basename = basename( TESTS_PLUGIN_DIR ) . '/ai-services.php';
		$this->services_loader = new Services_Loader( TESTS_PLUGIN_DIR . '/ai-services.php' );
	}

	/**
	 * @covers Services_Loader::add_hooks
	 */
	public function test_add_hooks() {
		$actions_to_check = array(
			'plugins_loaded',
			'init',
			'rest_api_init',
			'admin_menu',
		);
		foreach ( $actions_to_check as $hook_name ) {
			remove_all_actions( $hook_name );
		}

		$this->services_loader->add_hooks();

		foreach ( $actions_to_check as $hook_name ) {
			$this->assertHasAction( $hook_name );
		}
	}

	/**
	 * @covers Services_Loader::load_capabilities
	 */
	public function test_load_capabilities() {
		$container             = $this->getInaccessibleProperty( $this->services_loader, 'container' );
		$capability_controller = $container->get( 'capability_controller' );

		// Ensure the action is fired and receives the capability controller.
		$controller_passed_to_action_hook = null;
		add_action(
			'ais_load_services_capabilities',
			static function ( $controller ) use ( &$controller_passed_to_action_hook ) {
				$controller_passed_to_action_hook = $controller;
			}
		);

		remove_all_actions( 'plugins_loaded' );
		$this->callInaccessibleMethod( $this->services_loader, 'load_capabilities' );
		do_action( 'plugins_loaded' );

		$this->assertSame( $capability_controller, $controller_passed_to_action_hook );
	}

	/**
	 * @covers Services_Loader::load_dependencies
	 */
	public function test_load_dependencies() {
		$script_loader_mock = $this->createBasicMock( Services_Script_Style_Loader::class );

		$container = $this->getInaccessibleProperty( $this->services_loader, 'container' );
		$container->set(
			'services_script_style_loader',
			static function () use ( $script_loader_mock ) {
				return $script_loader_mock;
	 		}
		);

		$script_loader_mock->expects( $this->once() )->method( 'register_scripts_and_styles' );

		remove_all_actions( 'init' );
		$this->callInaccessibleMethod( $this->services_loader, 'load_dependencies' );
		do_action( 'init' );
	}

	/**
	 * @covers Services_Loader::load_options
	 */
	public function test_load_options() {
		$option_container_mock = $this->createBasicMock( Option_Container::class );

		$container = $this->getInaccessibleProperty( $this->services_loader, 'container' );
		$container->set(
			'option_container',
			static function () use ( $option_container_mock ) {
				return $option_container_mock;
	 		}
		);

		$option_container_mock->expects( $this->once() )->method( 'get_keys' );

		remove_all_actions( 'init' );
		$this->callInaccessibleMethod( $this->services_loader, 'load_options' );
		do_action( 'init' );
	}

	/**
	 * @covers Services_Loader::load_rest_routes
	 */
	public function test_load_rest_routes() {
		$route_collection_mock = $this->createBasicMock( REST_Route_Collection::class );

		$container = $this->getInaccessibleProperty( $this->services_loader, 'container' );
		$container->set(
			'rest_route_collection',
			static function () use ( $route_collection_mock ) {
				return $route_collection_mock;
	 		}
		);

		$route_collection_mock->expects( $this->once() )->method( 'getIterator' );

		remove_all_actions( 'rest_api_init' );
		$this->callInaccessibleMethod( $this->services_loader, 'load_rest_routes' );
		do_action( 'rest_api_init' );
	}

	/**
	 * @covers Services_Loader::load_settings_page
	 */
	public function test_load_settings_page() {
		$settings_menu_mock = $this->createBasicMock( Admin_Menu::class );

		$container = $this->getInaccessibleProperty( $this->services_loader, 'container' );
		$container->set(
			'admin_settings_menu',
			static function () use ( $settings_menu_mock ) {
				return $settings_menu_mock;
	 		}
		);

		$settings_menu_mock->expects( $this->once() )->method( 'add_page' );

		remove_all_actions( 'admin_menu' );
		$this->callInaccessibleMethod( $this->services_loader, 'load_settings_page' );
		do_action( 'admin_menu' );
	}
}
