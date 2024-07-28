<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Services_Loader
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Services;

use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Capability_Controller;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\With_Hooks;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Service_Container;

/**
 * Loader class responsible for initializing the AI services functionality, including its public API.
 *
 * @since n.e.x.t
 */
final class Services_Loader implements With_Hooks {

	/**
	 * Service container for the class's dependencies.
	 *
	 * @since n.e.x.t
	 * @var Service_Container
	 */
	private $container;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Plugin_Env $plugin_env Plugin environment object.
	 */
	public function __construct( Plugin_Env $plugin_env ) {
		$this->container = $this->set_up_container( $plugin_env );
		Services_API_Instance::set( $this->container['api'] );
	}

	/**
	 * Adds relevant WordPress hooks.
	 *
	 * @since n.e.x.t
	 */
	public function add_hooks(): void {
		$this->load_capabilities();
		$this->load_options();
		$this->load_settings_page();
	}

	/**
	 * Loads the services capabilities and sets up the relevant filters.
	 *
	 * @since n.e.x.t
	 */
	private function load_capabilities(): void {
		add_action(
			'plugins_loaded',
			function () {
				$controller = $this->container['capability_controller'];

				/**
				 * Fires when the services capabilities are loaded.
				 *
				 * This hook allows you to modify the rules for how these capabilities are granted. The capabilities
				 * available in the controller are:
				 * - 'wpoopple_manage_services' (base capability)
				 * - 'wpoopple_access_services' (base capability)
				 * - 'wpoopple_access_service' (meta capability, called with the specific service slug as parameter)
				 *
				 * @since n.e.x.t
				 *
				 * @param Capability_Controller $controller The capability controller, which can be used to modify the
				 *                                          rules for how capabilities are granted.
				 */
				do_action( 'wpoopple_load_services_capabilities', $controller );

				$this->container['capability_filters']->add_hooks();
			},
			0
		);
	}

	/**
	 * Loads the services options.
	 *
	 * The option container is populated with options dynamically based on registered AI services. Each of the relevant
	 * options will be registered here.
	 *
	 * @since n.e.x.t
	 */
	private function load_options(): void {
		add_action(
			'init',
			function () {
				$registry = $this->container['option_registry'];
				foreach ( $this->container['option_container']->get_keys() as $key ) {
					$option = $this->container['option_container']->get( $key );
					$registry->register(
						$option->get_key(),
						$option->get_registration_args()
					);
				}
			},
			0
		);
	}

	/**
	 * Loads the services settings page.
	 *
	 * @since n.e.x.t
	 */
	private function load_settings_page(): void {
		add_action(
			'admin_menu',
			function () {
				$this->container['admin_settings_menu']->add_page( $this->container['admin_settings_page'] );
			}
		);
	}

	/**
	 * Sets up the services service container.
	 *
	 * @since n.e.x.t
	 *
	 * @param Plugin_Env $plugin_env Plugin environment object.
	 * @return Service_Container The services service container.
	 */
	private function set_up_container( Plugin_Env $plugin_env ): Service_Container {
		$builder = new Services_Service_Container_Builder();

		return $builder->set_env( $plugin_env )
			->build_services()
			->get();
	}
}
