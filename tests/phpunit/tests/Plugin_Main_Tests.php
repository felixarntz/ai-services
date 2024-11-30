<?php
/**
 * Tests for Felix_Arntz\AI_Services\Plugin_Main
 *
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\PHPUnit\Tests;

use Felix_Arntz\AI_Services\Chatbot\Chatbot_Loader;
use Felix_Arntz\AI_Services\Installation\Plugin_Installer;
use Felix_Arntz\AI_Services\PHPUnit\Includes\Test_Case;
use Felix_Arntz\AI_Services\Plugin_Main;
use Felix_Arntz\AI_Services\Services\Services_API_Instance;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;

/**
 * @group plugin
 */
class Plugin_Main_Tests extends Test_Case {

	private $plugin_basename;
	private $plugin_main;

	public function set_up() {
		parent::set_up();

		// Clear up the singleton-like instance to prevent error from trying to set it again by via Plugin_Main.
		$this->setInaccessibleProperty( Services_API_Instance::class, 'instance', null );

		$this->plugin_basename = basename( TESTS_PLUGIN_DIR ) . '/ai-services.php';
		$this->plugin_main     = new Plugin_Main( TESTS_PLUGIN_DIR . '/ai-services.php' );
	}

	/**
	 * @covers Plugin_Main::add_hooks
	 */
	public function test_add_hooks() {
		$actions_to_check = array(
			// Via Plugin_Main.
			'init',
			"activate_{$this->plugin_basename}",
			"deactivate_{$this->plugin_basename}",

			// Via Services_Loader.
			'plugins_loaded',
			'rest_api_init',
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

	/**
	 * @covers Plugin_Main::maybe_install_data
	 */
	public function test_maybe_install_data_on_init_without_capability() {
		$installer_mock = $this->createBasicMock( Plugin_Installer::class );

		$container = $this->getInaccessibleProperty( $this->plugin_main, 'container' );
		$container->set(
			'plugin_installer',
			static function () use ( $installer_mock ) {
				return $installer_mock;
	 		}
		);

		$installer_mock->expects( $this->never() )->method( 'install' );

		remove_all_actions( 'init' );
		$this->callInaccessibleMethod( $this->plugin_main, 'maybe_install_data' );
		do_action( 'init' );
	}

	/**
	 * @covers Plugin_Main::maybe_install_data
	 */
	public function test_maybe_install_data_on_init_with_capability() {
		$installer_mock = $this->createBasicMock( Plugin_Installer::class );

		$container = $this->getInaccessibleProperty( $this->plugin_main, 'container' );
		$container->set(
			'plugin_installer',
			static function () use ( $installer_mock ) {
				return $installer_mock;
	 		}
		);

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		if ( is_multisite() ) {
			grant_super_admin( $admin_id );
		}
		wp_set_current_user( $admin_id );

		$installer_mock->expects( $this->once() )->method( 'install' );

		remove_all_actions( 'init' );
		$this->callInaccessibleMethod( $this->plugin_main, 'maybe_install_data' );
		do_action( 'init' );
	}

	/**
	 * @covers Plugin_Main::maybe_install_data
	 */
	public function test_maybe_install_data_on_activation_hook_single() {
		$installer_mock = $this->createBasicMock( Plugin_Installer::class );

		$container = $this->getInaccessibleProperty( $this->plugin_main, 'container' );
		$container->set(
			'plugin_installer',
			static function () use ( $installer_mock ) {
				return $installer_mock;
	 		}
		);

		$installer_mock->expects( $this->once() )->method( 'install' );

		remove_all_actions( "activate_{$this->plugin_basename}" );
		$this->callInaccessibleMethod( $this->plugin_main, 'maybe_install_data' );
		do_action( "activate_{$this->plugin_basename}", false );
	}

	/**
	 * @covers Plugin_Main::maybe_install_data
	 */
	public function test_maybe_install_data_on_activation_hook_network_wide() {
		$installer_mock = $this->createBasicMock( Plugin_Installer::class );

		$container = $this->getInaccessibleProperty( $this->plugin_main, 'container' );
		$container->set(
			'plugin_installer',
			static function () use ( $installer_mock ) {
				return $installer_mock;
	 		}
		);

		$installer_mock->expects( $this->never() )->method( 'install' );

		remove_all_actions( "activate_{$this->plugin_basename}" );
		$this->callInaccessibleMethod( $this->plugin_main, 'maybe_install_data' );
		do_action( "activate_{$this->plugin_basename}", true );
	}

	/**
	 * @covers Plugin_Main::add_cleanup_hooks
	 */
	public function test_add_cleanup_hooks_single() {
		if ( ! function_exists( 'wp_set_options_autoload' ) ) {
			$this->markTestSkipped( 'This test requires WordPress 6.4+.' );
		}

		update_option( 'ais_version', '1.0.0', true );
		update_option( 'ais_delete_data', true, false );

		$options = array(
			'ais_version',
			'ais_delete_data',
		);

		remove_all_actions( "deactivate_{$this->plugin_basename}" );
		remove_all_actions( "activate_{$this->plugin_basename}" );
		$this->callInaccessibleMethod( $this->plugin_main, 'add_cleanup_hooks' );

		// Deactivation should turn off autoloading for all options.
		do_action( "deactivate_{$this->plugin_basename}", false );
		$this->assertSameSetsWithIndex(
			array(
				'ais_version'     => false,
				'ais_delete_data' => false,
			),
			$this->get_option_autoload_values( $options )
		);

		// Reactivation should turn on autoloading again for options where it should be enabled.
		do_action( "activate_{$this->plugin_basename}", false );
		$this->assertSameSetsWithIndex(
			array(
				'ais_version'     => true,
				'ais_delete_data' => false,
			),
			$this->get_option_autoload_values( $options )
		);
	}

	/**
	 * @covers Plugin_Main::add_cleanup_hooks
	 */
	public function test_add_cleanup_hooks_network_wide() {
		if ( ! function_exists( 'wp_set_options_autoload' ) ) {
			$this->markTestSkipped( 'This test requires WordPress 6.4+.' );
		}

		update_option( 'ais_version', '1.0.0', true );
		update_option( 'ais_delete_data', true, false );

		$options = array(
			'ais_version',
			'ais_delete_data',
		);

		remove_all_actions( "deactivate_{$this->plugin_basename}" );
		remove_all_actions( "activate_{$this->plugin_basename}" );
		$this->callInaccessibleMethod( $this->plugin_main, 'add_cleanup_hooks' );

		// For network-wide deactivation, this shouldn't do anything.
		do_action( "deactivate_{$this->plugin_basename}", true );
		$this->assertSameSetsWithIndex(
			array(
				'ais_version'     => true,
				'ais_delete_data' => false,
			),
			$this->get_option_autoload_values( $options )
		);

		// And this shouldn't do anything either for network-wide activation.
		do_action( "activate_{$this->plugin_basename}", true );
		$this->assertSameSetsWithIndex(
			array(
				'ais_version'     => true,
				'ais_delete_data' => false,
			),
			$this->get_option_autoload_values( $options )
		);
	}

	/**
	 * @covers Plugin_Main::add_service_hooks
	 */
	public function test_add_service_hooks_for_options() {
		$option_container_mock = $this->createBasicMock( Option_Container::class );

		$container = $this->getInaccessibleProperty( $this->plugin_main, 'container' );
		$container->set(
			'option_container',
			static function () use ( $option_container_mock ) {
				return $option_container_mock;
	 		}
		);

		$option_container_mock->expects( $this->once() )->method( 'get_keys' );

		remove_all_actions( 'init' );
		$this->callInaccessibleMethod( $this->plugin_main, 'add_service_hooks' );
		do_action( 'init' );
	}

	/**
	 * @covers Plugin_Main::add_service_hooks
	 */
	public function test_add_service_hooks_for_chatbot() {
		$chatbot_loader_mock = $this->createBasicMock( Chatbot_Loader::class );

		$container = $this->getInaccessibleProperty( $this->plugin_main, 'container' );
		$container->set(
			'chatbot_loader',
			static function () use ( $chatbot_loader_mock ) {
				return $chatbot_loader_mock;
	 		}
		);

		$chatbot_loader_mock->expects( $this->once() )->method( 'can_load' );
		$chatbot_loader_mock->expects( $this->never() )->method( 'load' );

		remove_all_actions( 'init' );
		$this->callInaccessibleMethod( $this->plugin_main, 'add_service_hooks' );
		do_action( 'init' );
	}

	/**
	 * @covers Plugin_Main::add_service_hooks
	 */
	public function test_add_service_hooks_for_chatbot_enabled() {
		$chatbot_loader_mock = $this->createBasicMock( Chatbot_Loader::class );

		$container = $this->getInaccessibleProperty( $this->plugin_main, 'container' );
		$container->set(
			'chatbot_loader',
			static function () use ( $chatbot_loader_mock ) {
				return $chatbot_loader_mock;
	 		}
		);

		$chatbot_loader_mock->expects( $this->once() )->method( 'can_load' )->will( $this->returnValue( true ) );
		$chatbot_loader_mock->expects( $this->once() )->method( 'load' );

		remove_all_actions( 'init' );
		$this->callInaccessibleMethod( $this->plugin_main, 'add_service_hooks' );
		do_action( 'init' );
	}

	/**
	 * @covers Plugin_Main::register_default_services
	 */
	public function test_register_default_services() {
		// The `register_default_services()` method is called in the constructor, so we can simply check what it did.
		$services_api = $this->getInaccessibleProperty( $this->plugin_main, 'services_api' );

		$this->assertTrue( $services_api->is_service_registered( 'anthropic' ) );
		$this->assertTrue( $services_api->is_service_registered( 'google' ) );
		$this->assertTrue( $services_api->is_service_registered( 'openai' ) );
		$this->assertFalse( $services_api->is_service_registered( 'invalid' ) );
	}

	private function get_option_autoload_values( array $options ): array {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, autoload FROM $wpdb->options WHERE option_name IN (" . implode( ',', array_fill( 0, count( $options ), '%s' ) ) . ')',
				$options
			)
		);

		$autoload_values = wp_autoload_values_to_autoload();

		$options = array();
		foreach ( $results as $row ) {
			$options[ $row->option_name ] = in_array( $row->autoload, $autoload_values, true );
		}
		return $options;
	}
}
