<?php
/**
 * Class Felix_Arntz\AI_Services\Plugin_Service_Container_Builder
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services;

use Felix_Arntz\AI_Services\Chatbot\Chatbot;
use Felix_Arntz\AI_Services\Chatbot\Chatbot_Loader;
use Felix_Arntz\AI_Services\Installation\Plugin_Installer;
use Felix_Arntz\AI_Services\Services\Services_API_Instance;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Input;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Network_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Service_Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Site_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Repository;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Validation\General_Validation_Rule_Builder;

/**
 * Plugin service container builder.
 *
 * @since 0.1.0
 */
final class Plugin_Service_Container_Builder {

	/**
	 * Service container.
	 *
	 * @since 0.1.0
	 * @var Service_Container
	 */
	private $container;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->container = new Service_Container();
	}

	/**
	 * Gets the service container.
	 *
	 * @since 0.1.0
	 *
	 * @return Service_Container Service container for the plugin.
	 */
	public function get(): Service_Container {
		return $this->container;
	}

	/**
	 * Builds the plugin environment service for the service container.
	 *
	 * @since 0.1.0
	 *
	 * @param string $main_file Absolute path to the plugin main file.
	 * @return self The builder instance, for chaining.
	 */
	public function build_env( string $main_file ): self {
		$this->container['plugin_env'] = function () use ( $main_file ) {
			return new Plugin_Env( $main_file, AI_SERVICES_VERSION );
		};

		return $this;
	}

	/**
	 * Builds the services for the service container.
	 *
	 * @since 0.1.0
	 *
	 * @return self The builder instance, for chaining.
	 */
	public function build_services(): self {
		$this->build_general_services();
		$this->build_dependency_services();
		$this->build_option_services();

		$this->container['chatbot_loader'] = static function () {
			return new Chatbot_Loader(
				Services_API_Instance::get()
			);
		};
		$this->container['chatbot']        = static function ( $cont ) {
			return new Chatbot(
				$cont['plugin_env'],
				$cont['site_env'],
				$cont['network_env'],
				$cont['current_user'],
				$cont['script_registry'],
				$cont['style_registry']
			);
		};

		return $this;
	}

	/**
	 * Builds the general services for the service container.
	 *
	 * @since 0.1.0
	 */
	private function build_general_services(): void {
		$this->container['input']            = static function () {
			return new Input();
		};
		$this->container['current_user']     = static function () {
			return new Current_User();
		};
		$this->container['site_env']         = static function () {
			return new Site_Env();
		};
		$this->container['network_env']      = static function () {
			return new Network_Env();
		};
		$this->container['plugin_installer'] = static function ( $cont ) {
			return new Plugin_Installer(
				$cont['plugin_env'],
				$cont['option_container']['ais_version'],
				$cont['option_container']['ais_delete_data']
			);
		};
	}

	/**
	 * Builds the dependency services for the service container.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
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
			return new Option_Registry( 'ai_services' );
		};
	}

	/**
	 * Adds the option definitions to the given option container.
	 *
	 * @since 0.1.0
	 *
	 * @param Option_Container $options Option container instance.
	 */
	private function add_options_to_container( Option_Container $options ): void {
		// Option to control plugin version.
		$options['ais_version'] = function () {
			$sanitize_callback = ( new General_Validation_Rule_Builder() )
				->require_string()
				->format_version()
				->get_option_sanitize_callback();

			return new Option(
				$this->container['option_repository'],
				'ais_version',
				array(
					'type'              => 'string',
					'sanitize_callback' => $sanitize_callback,
					'default'           => '',
					'autoload'          => true,
				)
			);
		};

		// Option for whether to delete data on uninstall.
		$options['ais_delete_data'] = function () {
			$sanitize_callback = ( new General_Validation_Rule_Builder() )
				->require_boolean()
				->get_option_sanitize_callback();

			return new Option(
				$this->container['option_repository'],
				'ais_delete_data',
				array(
					'type'              => 'boolean',
					'sanitize_callback' => $sanitize_callback,
					'default'           => false,
					'show_in_rest'      => true,
					'autoload'          => false,
				)
			);
		};
	}
}
