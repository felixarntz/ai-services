<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Plugin_Service_Container_Builder
 *
 * @since n.e.x.t
 * @package wp-oop-plugin-lib-example
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example;

use Vendor_NS\WP_OOP_Plugin_Lib_Example\Chatbot\Chatbot;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Chatbot\Chatbot_AI;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Chatbot\Chatbot_Loader;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Gemini_AI_Service;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Installation\Plugin_Installer;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Entities\Post_Repository;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Input;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Service_Container;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Registry;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Repository;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Validation\General_Validation_Rule_Builder;

/**
 * Plugin service container builder.
 *
 * @since n.e.x.t
 */
final class Plugin_Service_Container_Builder {

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
		$this->build_dependency_services();
		$this->build_option_services();
		$this->build_entity_services();

		$this->container['chatbot_loader'] = static function ( $cont ) {
			return new Chatbot_Loader(
				$cont['current_user'],
				$cont['option_container']['wpoopple_api_key']
			);
		};
		$this->container['chatbot_ai']     = static function ( $cont ) {
			return new Chatbot_AI( $cont['generative_ai'] );
		};
		$this->container['chatbot']        = static function ( $cont ) {
			return new Chatbot(
				$cont['plugin_env'],
				$cont['script_registry'],
				$cont['style_registry']
			);
		};
		$this->container['generative_ai']  = static function ( $cont ) {
			return new Gemini_AI_Service(
				$cont['option_container']['wpoopple_api_key']->get_value()
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
		$this->container['input']            = static function () {
			return new Input();
		};
		$this->container['current_user']     = static function () {
			return new Current_User();
		};
		$this->container['plugin_installer'] = static function ( $cont ) {
			return new Plugin_Installer(
				$cont['plugin_env'],
				$cont['option_container']['wpoopple_version'],
				$cont['option_container']['wpoopple_delete_data']
			);
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
		$this->container['option_container']  = function () {
			$options = new Option_Container();
			$this->add_options_to_container( $options );
			return $options;
		};
		$this->container['option_registry']   = static function () {
			return new Option_Registry( 'wp_oop_plugin_lib_example' );
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
	 * Adds the option definitions to the given option container.
	 *
	 * @since n.e.x.t
	 *
	 * @param Option_Container $options Option container instance.
	 */
	private function add_options_to_container( Option_Container $options ): void {
		// Option to control plugin version.
		$options['wpoopple_version'] = function () {
			$sanitize_callback = ( new General_Validation_Rule_Builder() )
				->require_string()
				->format_version()
				->get_option_sanitize_callback();

			return new Option(
				$this->container['option_repository'],
				'wpoopple_version',
				array(
					'type'              => 'string',
					'sanitize_callback' => $sanitize_callback,
					'default'           => '',
					'autoload'          => true,
				)
			);
		};

		// Option for whether to delete data on uninstall.
		$options['wpoopple_delete_data'] = function () {
			$sanitize_callback = ( new General_Validation_Rule_Builder() )
				->require_boolean()
				->get_option_sanitize_callback();

			return new Option(
				$this->container['option_repository'],
				'wpoopple_delete_data',
				array(
					'type'              => 'bool',
					'sanitize_callback' => $sanitize_callback,
					'default'           => false,
					'autoload'          => false,
				)
			);
		};

		$options['wpoopple_api_key'] = function () {
			$sanitize_callback = ( new General_Validation_Rule_Builder() )
				->require_string()
				->format_regexp( '/^[A-Za-z0-9-]+$/' )
				->get_option_sanitize_callback();

			return new Option(
				$this->container['option_repository'],
				'wpoopple_api_key',
				array(
					'type'              => 'string',
					'sanitize_callback' => $sanitize_callback,
					'default'           => 'AIzaSyAwWr7iLmcF--aExgltOno8ppxdxPac5bQ', // TODO: Remove this.
					'autoload'          => true,
				)
			);
		};

		// Option to store the main plugin data.
		$options['wpoopple_options'] = function () {
			return new Option(
				$this->container['option_repository'],
				'wpoopple_options',
				array(
					'type'     => 'object',
					'default'  => array(),
					'autoload' => true,
				)
			);
		};
	}
}
