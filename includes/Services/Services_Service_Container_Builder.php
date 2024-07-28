<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Services_Service_Container_Builder
 *
 * @since n.e.x.t
 * @package wp-oop-plugin-lib-example
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Services;

use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Admin\Settings_Page;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Admin_Menu;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Base_Capability;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Capability_Container;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Capability_Controller;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Capability_Filters;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Meta_Capability;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Service_Container;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Registry;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Repository;

/**
 * Service container builder for the services loader.
 *
 * @since n.e.x.t
 */
final class Services_Service_Container_Builder {

	/**
	 * Service container.
	 *
	 * @since n.e.x.t
	 * @var Service_Container
	 */
	private $container;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 */
	public function __construct() {
		$this->container = new Service_Container();
	}

	/**
	 * Gets the service container.
	 *
	 * @since n.e.x.t
	 *
	 * @return Service_Container Service container for the plugin.
	 */
	public function get(): Service_Container {
		return $this->container;
	}

	/**
	 * Sets the plugin environment service in the service container.
	 *
	 * @since n.e.x.t
	 *
	 * @param Plugin_Env $plugin_env Plugin environment object.
	 * @return self The builder instance, for chaining.
	 */
	public function set_env( Plugin_Env $plugin_env ): self {
		$this->container['plugin_env'] = function () use ( $plugin_env ) {
			return $plugin_env;
		};

		return $this;
	}

	/**
	 * Builds the services for the service container.
	 *
	 * @since n.e.x.t
	 *
	 * @return self The builder instance, for chaining.
	 */
	public function build_services(): self {
		$this->build_general_services();
		$this->build_capability_services();
		$this->build_dependency_services();
		$this->build_option_services();
		$this->build_admin_services();

		$this->container['api'] = static function ( $cont ) {
			return new Services_API(
				$cont['current_user'],
				$cont['option_container'],
				$cont['option_repository']
			);
		};

		return $this;
	}

	/**
	 * Builds the general services for the service container.
	 *
	 * @since n.e.x.t
	 */
	private function build_general_services(): void {
		$this->container['current_user'] = static function () {
			return new Current_User();
		};
	}

	/**
	 * Builds the capability services for the service container.
	 *
	 * @since n.e.x.t
	 */
	private function build_capability_services(): void {
		$this->container['capability_container'] = static function () {
			$capabilities                             = new Capability_Container();
			$capabilities['wpoopple_manage_services'] = static function () {
				return new Base_Capability(
					'wpoopple_manage_services',
					array( 'manage_options' )
				);
			};
			$capabilities['wpoopple_access_services'] = static function () {
				return new Base_Capability(
					'wpoopple_access_services',
					array( 'edit_posts' )
				);
			};
			$capabilities['wpoopple_access_service']  = static function () {
				return new Meta_Capability(
					'wpoopple_access_service',
					// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
					static function ( int $user_id, string $service_slug ) {
						return array( 'wpoopple_access_services' );
					}
				);
			};
			return $capabilities;
		};

		$this->container['capability_controller'] = static function ( $cont ) {
			return new Capability_Controller( $cont['capability_container'] );
		};
		$this->container['capability_filters']    = static function ( $cont ) {
			return new Capability_Filters( $cont['capability_container'] );
		};
	}

	/**
	 * Builds the dependency services for the service container.
	 *
	 * @since n.e.x.t
	 */
	private function build_dependency_services(): void {
		$this->container['script_registry'] = static function () {
			return new Script_Registry();
		};
		$this->container['style_registry']  = static function () {
			return new Style_Registry();
		};
	}

	/**
	 * Builds the option services for the service container.
	 *
	 * @since n.e.x.t
	 */
	private function build_option_services(): void {
		$this->container['option_repository'] = static function () {
			return new Option_Repository();
		};
		$this->container['option_container']  = static function () {
			return new Option_Container();
		};
		$this->container['option_registry']   = static function () {
			return new Option_Registry( 'wpoopple_services' );
		};
	}

	/**
	 * Builds the admin services for the service container.
	 *
	 * @since n.e.x.t
	 */
	private function build_admin_services(): void {
		$this->container['admin_settings_menu'] = static function () {
			return new Admin_Menu( 'options-general.php' );
		};
		$this->container['admin_settings_page'] = static function ( $cont ) {
			return new Settings_Page(
				$cont['plugin_env'],
				$cont['script_registry']
			);
		};
	}
}
