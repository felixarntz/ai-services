<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Plugin_Service_Container_Builder
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin;

use Vendor_NS\WP_Starter_Plugin\Admin\Settings_Page;
use Vendor_NS\WP_Starter_Plugin\Dependencies\Plugin_Script_Style_Loader;
use Vendor_NS\WP_Starter_Plugin\Installation\Plugin_Installer;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Admin_Menu;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Entities\Post_Repository;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Input;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Service_Container;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Repository;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Validation\General_Validation_Rule_Builder;

/**
 * Plugin service container builder.
 *
 * @since n.e.x.t
 */
class Plugin_Service_Container_Builder {

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
	 * Builds the plugin environment service for the service container.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 * @return self The builder instance, for chaining.
	 */
	public function build_env( string $main_file ): self {
		$this->container['plugin_env'] = function () use ( $main_file ) {
			return new Plugin_Env( $main_file, WP_STARTER_PLUGIN_VERSION );
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
		$this->build_dependency_services();
		$this->build_option_services();
		$this->build_entity_services();
		$this->build_admin_services();

		return $this;
	}

	/**
	 * Builds the general services for the service container.
	 *
	 * @since n.e.x.t
	 */
	private function build_general_services(): void {
		$this->container['input']            = static function () {
			return new Input();
		};
		$this->container['current_user']     = static function () {
			return new Current_User();
		};
		$this->container['plugin_installer'] = static function ( $cont ) {
			return new Plugin_Installer(
				$cont['plugin_env'],
				$cont['option_container']['wpsp_version'],
				$cont['option_container']['wpsp_delete_data'],
				$cont['option_container']['wpsp_options']
			);
		};
	}

	/**
	 * Builds the dependency services for the service container.
	 *
	 * @since n.e.x.t
	 */
	private function build_dependency_services(): void {
		$this->container['script_registry']            = static function () {
			return new Script_Registry();
		};
		$this->container['style_registry']             = static function () {
			return new Style_Registry();
		};
		$this->container['plugin_script_style_loader'] = static function ( $cont ) {
			return new Plugin_Script_Style_Loader(
				$cont['plugin_env'],
				$cont['script_registry'],
				$cont['style_registry']
			);
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
		$this->container['option_container']  = function () {
			$options = new Option_Container();
			$this->add_options_to_container( $options );
			return $options;
		};
		$this->container['option_registry']   = static function () {
			return new Option_Registry( 'wp_starter_plugin' );
		};
	}

	/**
	 * Builds the entity services for the service container.
	 *
	 * @since n.e.x.t
	 */
	private function build_entity_services(): void {
		$this->container['post_repository'] = static function () {
			return new Post_Repository();
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
				$cont['script_registry'],
				$cont['style_registry']
			);
		};
	}

	/**
	 * Adds the option definitions to the given option container.
	 *
	 * @since n.e.x.t
	 *
	 * @param Option_Container $options Option container instance.
	 */
	private function add_options_to_container( Option_Container $options ): void {
		// Option to control plugin version.
		$options['wpsp_version'] = function () {
			$sanitize_callback = ( new General_Validation_Rule_Builder() )
				->require_string()
				->format_version()
				->get_option_sanitize_callback();

			return new Option(
				$this->container['option_repository'],
				'wpsp_version',
				array(
					'type'              => 'string',
					'sanitize_callback' => $sanitize_callback,
					'default'           => '',
					'autoload'          => true,
				)
			);
		};

		// Option for whether to delete data on uninstall.
		$options['wpsp_delete_data'] = function () {
			$sanitize_callback = ( new General_Validation_Rule_Builder() )
				->require_boolean()
				->get_option_sanitize_callback();

			return new Option(
				$this->container['option_repository'],
				'wpsp_delete_data',
				array(
					'type'              => 'boolean',
					'sanitize_callback' => $sanitize_callback,
					'default'           => false,
					'show_in_rest'      => true,
					'autoload'          => false,
				)
			);
		};

		// Option to store the main plugin data.
		$options['wpsp_options'] = function () {
			return new Option(
				$this->container['option_repository'],
				'wpsp_options',
				array(
					'type'     => 'object',
					'default'  => array(),
					'autoload' => true,
				)
			);
		};
	}
}
