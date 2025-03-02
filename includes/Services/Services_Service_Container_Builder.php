<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Services_Service_Container_Builder
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services;

use Felix_Arntz\AI_Services\Services\Admin\Playground_Page;
use Felix_Arntz\AI_Services\Services\Admin\Playground_Page_Pointer;
use Felix_Arntz\AI_Services\Services\Admin\Settings_Page;
use Felix_Arntz\AI_Services\Services\Admin\Settings_Page_Link;
use Felix_Arntz\AI_Services\Services\Admin\Settings_Page_Pointer;
use Felix_Arntz\AI_Services\Services\CLI\AI_Services_Command;
use Felix_Arntz\AI_Services\Services\Dependencies\Services_Script_Style_Loader;
use Felix_Arntz\AI_Services\Services\HTTP\HTTP_With_Streams;
use Felix_Arntz\AI_Services\Services\Options\Option_Encrypter;
use Felix_Arntz\AI_Services\Services\REST_Routes\History_Delete_REST_Route;
use Felix_Arntz\AI_Services\Services\REST_Routes\History_Get_REST_Route;
use Felix_Arntz\AI_Services\Services\REST_Routes\History_List_REST_Route;
use Felix_Arntz\AI_Services\Services\REST_Routes\History_REST_Resource_Schema;
use Felix_Arntz\AI_Services\Services\REST_Routes\History_Update_REST_Route;
use Felix_Arntz\AI_Services\Services\REST_Routes\Self_REST_Route;
use Felix_Arntz\AI_Services\Services\REST_Routes\Service_Generate_Image_REST_Route;
use Felix_Arntz\AI_Services\Services\REST_Routes\Service_Generate_Text_REST_Route;
use Felix_Arntz\AI_Services\Services\REST_Routes\Service_Get_REST_Route;
use Felix_Arntz\AI_Services\Services\REST_Routes\Service_List_REST_Route;
use Felix_Arntz\AI_Services\Services\REST_Routes\Service_REST_Resource_Schema;
use Felix_Arntz\AI_Services\Services\REST_Routes\Service_Stream_Generate_Text_REST_Route;
use Felix_Arntz\AI_Services\Services\Util\Data_Encryption;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Links\Admin_Link_Collection;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Links\Admin_Page_Link;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Links\Plugin_Action_Links;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Admin_Menu;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pointers\Admin_Pointer_Collection;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pointers\Admin_Pointer_Loader;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Base_Capability;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Capability_Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Capability_Controller;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Capability_Filters;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Meta_Capability;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Service_Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Site_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Meta\Meta_Repository;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Container;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Options\Option_Repository;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Aggregate_REST_Route;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\REST_Namespace;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\REST_Route_Collection;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\REST_Route_Registry;

/**
 * Service container builder for the services loader.
 *
 * @since 0.1.0
 */
final class Services_Service_Container_Builder {

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
		$this->build_capability_services();
		$this->build_dependency_services();
		$this->build_http_services();
		$this->build_option_services();
		$this->build_entity_services();
		$this->build_rest_services();
		$this->build_admin_services();
		$this->build_cli_services();

		$this->container['api'] = static function ( $cont ) {
			return new Services_API(
				$cont['current_user'],
				$cont['option_container'],
				$cont['option_repository'],
				$cont['option_encrypter'],
				$cont['http']
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
		$this->container['current_user'] = static function () {
			return new Current_User();
		};
		$this->container['site_env']     = static function () {
			return new Site_Env();
		};
	}

	/**
	 * Builds the capability services for the service container.
	 *
	 * @since 0.1.0
	 */
	private function build_capability_services(): void {
		$this->container['capability_container'] = static function () {
			$capabilities                        = new Capability_Container();
			$capabilities['ais_manage_services'] = static function () {
				return new Base_Capability(
					'ais_manage_services',
					array( 'manage_options' )
				);
			};
			$capabilities['ais_access_services'] = static function () {
				return new Base_Capability(
					'ais_access_services',
					array( 'edit_posts' )
				);
			};
			$capabilities['ais_access_service']  = static function () {
				return new Meta_Capability(
					'ais_access_service',
					// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
					static function ( int $user_id, string $service_slug ) {
						return array( 'ais_access_services' );
					}
				);
			};
			$capabilities['ais_use_playground']  = static function () {
				return new Meta_Capability(
					'ais_use_playground',
					// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
					static function ( int $user_id ) {
						return array( 'ais_access_services' );
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
	 * @since 0.1.0
	 */
	private function build_dependency_services(): void {
		$this->container['script_registry']              = static function () {
			return new Script_Registry();
		};
		$this->container['style_registry']               = static function () {
			return new Style_Registry();
		};
		$this->container['services_script_style_loader'] = static function ( $cont ) {
			return new Services_Script_Style_Loader(
				$cont['plugin_env'],
				$cont['script_registry'],
				$cont['style_registry']
			);
		};
	}

	/**
	 * Builds the HTTP services for the service container.
	 *
	 * @since 0.1.0
	 */
	private function build_http_services(): void {
		$this->container['http'] = static function () {
			// Custom implementation with additional support for streaming responses.
			return new HTTP_With_Streams();
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
		$this->container['option_container']  = static function () {
			return new Option_Container();
		};
		$this->container['option_registry']   = static function () {
			return new Option_Registry( 'ais_services' );
		};
		$this->container['option_encrypter']  = static function () {
			return new Option_Encrypter( new Data_Encryption() );
		};
	}

	/**
	 * Builds the entity services for the service container.
	 *
	 * @since n.e.x.t
	 */
	private function build_entity_services(): void {
		$this->container['user_meta_repository'] = static function () {
			return new Meta_Repository( 'user' );
		};
	}

	/**
	 * Builds the REST services for the service container.
	 *
	 * @since 0.1.0
	 */
	private function build_rest_services(): void {
		$this->container['rest_namespace']        = function () {
			return new REST_Namespace( 'ai-services/v1' );
		};
		$this->container['rest_route_collection'] = function ( $cont ) {
			$service_resource_schema = new Service_REST_Resource_Schema( $cont['rest_namespace'] );
			$history_resource_schema = new History_REST_Resource_Schema( $cont['rest_namespace'] );

			return new REST_Route_Collection(
				array(
					new Self_REST_Route(
						$cont['plugin_env'],
						$cont['site_env'],
						$cont['api'],
						$cont['current_user'],
						$cont['admin_settings_menu'],
						$cont['admin_tools_menu'],
						$cont['admin_settings_page'],
						$cont['admin_playground_page']
					),
					new Service_List_REST_Route( $cont['api'], $cont['current_user'], $service_resource_schema ),
					new Service_Get_REST_Route( $cont['api'], $cont['current_user'], $service_resource_schema ),
					new Service_Generate_Text_REST_Route( $cont['api'], $cont['current_user'] ),
					new Service_Stream_Generate_Text_REST_Route( $cont['api'], $cont['current_user'] ),
					new Service_Generate_Image_REST_Route( $cont['api'], $cont['current_user'] ),
					new History_List_REST_Route( $cont['current_user'], $history_resource_schema ),
					new Aggregate_REST_Route(
						array(
							new History_Get_REST_Route( $cont['current_user'], $history_resource_schema ),
							new History_Update_REST_Route( $cont['current_user'], $history_resource_schema ),
							new History_Delete_REST_Route( $cont['current_user'], $history_resource_schema ),
						)
					),
				)
			);
		};
		$this->container['rest_route_registry']   = function ( $cont ) {
			return new REST_Route_Registry( $cont['rest_namespace'] );
		};
	}

	/**
	 * Builds the admin services for the service container.
	 *
	 * @since 0.1.0
	 */
	private function build_admin_services(): void {
		$this->container['admin_settings_menu']           = static function () {
			return new Admin_Menu( 'options-general.php' );
		};
		$this->container['admin_tools_menu']              = static function () {
			return new Admin_Menu( 'tools.php' );
		};
		$this->container['admin_settings_page']           = static function ( $cont ) {
			return new Settings_Page(
				$cont['script_registry'],
				$cont['style_registry']
			);
		};
		$this->container['admin_playground_page']         = static function ( $cont ) {
			return new Playground_Page(
				$cont['script_registry'],
				$cont['style_registry']
			);
		};
		$this->container['admin_settings_page_link']      = static function ( $cont ) {
			return new Settings_Page_Link(
				$cont['admin_settings_menu'],
				$cont['admin_settings_page'],
				$cont['site_env']
			);
		};
		$this->container['admin_playground_page_link']    = static function ( $cont ) {
			return new Admin_Page_Link(
				$cont['admin_tools_menu'],
				$cont['admin_playground_page'],
				$cont['site_env']
			);
		};
		$this->container['admin_link_collection']         = static function ( $cont ) {
			return new Admin_Link_Collection(
				array(
					$cont['admin_settings_page_link'],
					$cont['admin_playground_page_link'],
				)
			);
		};
		$this->container['plugin_action_links']           = static function ( $cont ) {
			return new Plugin_Action_Links(
				$cont['admin_link_collection'],
				$cont['current_user']
			);
		};
		$this->container['admin_settings_page_pointer']   = static function ( $cont ) {
			return new Settings_Page_Pointer(
				$cont['admin_settings_page_link'],
				$cont['api']
			);
		};
		$this->container['admin_playground_page_pointer'] = static function ( $cont ) {
			return new Playground_Page_Pointer(
				$cont['admin_playground_page_link'],
				$cont['api']
			);
		};
		$this->container['admin_pointer_collection']      = static function ( $cont ) {
			return new Admin_Pointer_Collection(
				array(
					$cont['admin_settings_page_pointer'],
					$cont['admin_playground_page_pointer'],
				)
			);
		};
		$this->container['admin_pointer_loader']          = static function ( $cont ) {
			return new Admin_Pointer_Loader(
				$cont['admin_pointer_collection'],
				$cont['script_registry'],
				$cont['style_registry'],
				$cont['user_meta_repository'],
				$cont['current_user']
			);
		};
	}

	/**
	 * Builds the CLI services for the service container.
	 *
	 * @since 0.2.0
	 */
	private function build_cli_services(): void {
		$this->container['cli_command'] = static function ( $cont ) {
			return new AI_Services_Command(
				$cont['api'],
				$cont['current_user'],
				$cont['capability_controller']
			);
		};
	}
}
