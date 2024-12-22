<?php
/**
 * Class Felix_Arntz\AI_Services\Services\REST_Routes\Self_REST_Route
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\REST_Routes;

use Felix_Arntz\AI_Services\Services\Services_API;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Admin_Menu;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Contracts\Admin_Page;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Site_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Abstract_REST_Route;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\REST_Routes\Exception\REST_Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class representing the REST API route for getting general data for the plugin in context of the current user.
 *
 * @since n.e.x.t
 */
class Self_REST_Route extends Abstract_REST_Route {
	const BASE    = '/self';
	const METHODS = WP_REST_Server::READABLE;

	/**
	 * The plugin environment.
	 *
	 * @since n.e.x.t
	 * @var Plugin_Env
	 */
	private $plugin_env;

	/**
	 * Site environment.
	 *
	 * @since n.e.x.t
	 * @var Site_Env
	 */
	private $site_env;

	/**
	 * The services API instance.
	 *
	 * @since n.e.x.t
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * Current user service.
	 *
	 * @since n.e.x.t
	 * @var Current_User
	 */
	private $current_user;

	/**
	 * WordPress admin settings menu.
	 *
	 * @since n.e.x.t
	 * @var Admin_Menu
	 */
	private $settings_menu;

	/**
	 * WordPress admin tools menu.
	 *
	 * @since n.e.x.t
	 * @var Admin_Menu
	 */
	private $tools_menu;

	/**
	 * The plugin's admin settings page.
	 *
	 * @since n.e.x.t
	 * @var Admin_Page
	 */
	private $settings_page;

	/**
	 * The plugin's admin playground page.
	 *
	 * @since n.e.x.t
	 * @var Admin_Page
	 */
	private $playground_page;

	/**
	 * Internal resource schema definition.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $schema;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Plugin_Env   $plugin_env      The plugin environment.
	 * @param Site_Env     $site_env        The site environment.
	 * @param Services_API $services_api    The services API instance.
	 * @param Current_User $current_user    The current user service.
	 * @param Admin_Menu   $settings_menu   WordPress admin settings menu.
	 * @param Admin_Menu   $tools_menu      WordPress admin tools menu.
	 * @param Admin_Page   $settings_page   The plugin's admin settings page.
	 * @param Admin_Page   $playground_page The plugin's admin playground page.
	 */
	public function __construct(
		Plugin_Env $plugin_env,
		Site_Env $site_env,
		Services_API $services_api,
		Current_User $current_user,
		Admin_Menu $settings_menu,
		Admin_Menu $tools_menu,
		Admin_Page $settings_page,
		Admin_Page $playground_page
	) {
		$this->plugin_env      = $plugin_env;
		$this->site_env        = $site_env;
		$this->services_api    = $services_api;
		$this->current_user    = $current_user;
		$this->settings_menu   = $settings_menu;
		$this->tools_menu      = $tools_menu;
		$this->settings_page   = $settings_page;
		$this->playground_page = $playground_page;

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'ai-services-plugin',
			'type'       => 'object',
			'properties' => array(
				'plugin_slug'               => array(
					'description' => __( 'Plugin slug.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'plugin_basename'           => array(
					'description' => __( 'Plugin basename.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'plugin_version'            => array(
					'description' => __( 'Plugin version.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'plugin_homepage_url'       => array(
					'description' => __( 'Plugin homepage URL.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'plugin_support_url'        => array(
					'description' => __( 'Plugin support URL.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'plugin_contributing_url'   => array(
					'description' => __( 'Plugin support URL.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'plugin_settings_url'       => array(
					'description' => __( 'AI Services settings URL.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'plugin_playground_url'     => array(
					'description' => __( 'AI Playground URL.', 'ai-services' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'current_user_capabilities' => array(
					'description'          => __( 'Map of the plugin capabilities and whether they are granted for the current user.', 'ai-services' ),
					'type'                 => 'object',
					'context'              => array( 'view', 'edit' ),
					'readonly'             => true,
					'properties'           => array(),
					'additionalProperties' => array( 'type' => 'boolean' ),
				),
			),
		);

		parent::__construct();
	}

	/**
	 * Returns the route base.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Route base.
	 */
	protected function base(): string {
		return self::BASE;
	}

	/**
	 * Returns the route methods, as a comma-separated string.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Route methods, as a comma-separated string.
	 */
	protected function methods(): string {
		return self::METHODS;
	}

	/**
	 * Checks the required permissions for the given request and throws an exception if they aren't met.
	 *
	 * @since n.e.x.t
	 *
	 * @param WP_REST_Request $request WordPress REST request object, including parameters.
	 *
	 * @throws REST_Exception Thrown when the permissions aren't met, or when a REST error occurs.
	 */
	protected function check_permissions( WP_REST_Request $request ): void /* @phpstan-ignore-line */ {
		if ( ! $this->current_user->has_cap( 'ais_access_services' ) ) {
			throw REST_Exception::create(
				'rest_cannot_view',
				esc_html__( 'Sorry, you are not allowed to access services.', 'ai-services' ),
				$this->current_user->is_logged_in() ? 403 : 401
			);
		}
	}

	/**
	 * Handles the given request and returns a response.
	 *
	 * @since n.e.x.t
	 *
	 * @param WP_REST_Request $request WordPress REST request object, including parameters.
	 * @return WP_REST_Response WordPress REST response object.
	 *
	 * @throws REST_Exception Thrown when a REST error occurs.
	 */
	protected function handle_request( WP_REST_Request $request ): WP_REST_Response /* @phpstan-ignore-line */ {
		$fields = $this->get_fields_to_include( $request );

		$data = array();
		foreach ( $fields as $field ) {
			switch ( $field ) {
				case 'plugin_slug':
					$value = 'ai-services';
					break;
				case 'plugin_basename':
					$value = $this->plugin_env->basename();
					break;
				case 'plugin_version':
					$value = $this->plugin_env->version();
					break;
				case 'plugin_homepage_url':
					$value = __( 'https://wordpress.org/plugins/ai-services/', 'ai-services' );
					break;
				case 'plugin_support_url':
					$value = __( 'https://wordpress.org/support/plugin/ai-services/', 'ai-services' );
					break;
				case 'plugin_contributing_url':
					$value = 'https://github.com/felixarntz/ai-services';
					break;
				case 'plugin_settings_url':
					$value = $this->get_admin_url( $this->settings_menu, $this->settings_page );
					break;
				case 'plugin_playground_url':
					$value = $this->get_admin_url( $this->tools_menu, $this->playground_page );
					break;
				case 'current_user_capabilities':
					$value = $this->get_current_user_capabilities();
					break;
				default:
					throw REST_Exception::create(
						'rest_invalid_field',
						esc_html(
							sprintf(
								/* translators: %s: field name */
								__( 'Invalid field: %s.', 'ai-services' ),
								$field
							)
						),
						400
					);
			}
			$data[ $field ] = $value;
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Returns the route specific arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Route arguments.
	 */
	protected function args(): array {
		return array(
			'context' => array(
				'description'       => __( 'Scope under which the request is made; determines fields present in response.', 'default' ),
				'type'              => 'string',
				'enum'              => array( 'view', 'edit' ),
				'default'           => 'view',
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	}

	/**
	 * Returns the global route arguments.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> Global route arguments.
	 */
	protected function global_args(): array {
		return array(
			'allow_batch' => array( 'v1' => true ),
			'schema'      => $this->schema,
		);
	}

	/**
	 * Gets an array of fields to be included on the response.
	 *
	 * Included fields are based on item schema and `_fields=` request argument.
	 *
	 * This is mostly a copy of {@see WP_REST_Controller::get_fields_for_response()}.
	 *
	 * @since n.e.x.t
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return string[] Fields to be included in the response.
	 */
	protected function get_fields_to_include( WP_REST_Request $request ): array /* @phpstan-ignore-line */ {
		$properties = $this->schema['properties'] ?? array();

		// Exclude fields that specify a different context than the request context.
		$context = $request['context'];
		if ( $context ) {
			foreach ( $properties as $name => $options ) {
				if ( ! empty( $options['context'] ) && ! in_array( $context, $options['context'], true ) ) {
					unset( $properties[ $name ] );
				}
			}
		}

		$fields = array_keys( $properties );

		$requested_fields = wp_parse_list( $request['_fields'] );
		if ( 0 === count( $requested_fields ) ) {
			return $fields;
		}
		// Trim off outside whitespace from the comma delimited list.
		$requested_fields = array_map( 'trim', $requested_fields );

		// Return the list of all requested fields which appear in the schema.
		return array_values( array_intersect( $fields, $requested_fields ) );
	}

	/**
	 * Gets the URL to a specific admin page within a given admin menu.
	 *
	 * @since n.e.x.t
	 *
	 * @param Admin_Menu $menu WordPress admin menu.
	 * @param Admin_Page $page WordPress admin page.
	 * @return string Admin page URL.
	 */
	private function get_admin_url( Admin_Menu $menu, Admin_Page $page ): string {
		$menu_slug = $menu->get_slug();
		if ( str_ends_with( $menu_slug, '.php' ) ) {
			$menu_file = $menu_slug;
		} else {
			$menu_file = 'admin.php';
		}

		return add_query_arg(
			'page',
			$page->get_slug(),
			$this->site_env->admin_url( $menu_file )
		);
	}

	/**
	 * Gets the map of the plugin capabilities and whether they are granted for the current user.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, bool> Map of the plugin capabilities for the current user and whether they are granted.
	 */
	private function get_current_user_capabilities(): array {
		$capabilities = array(
			'ais_manage_services' => $this->current_user->has_cap( 'ais_manage_services' ),
			'ais_access_services' => $this->current_user->has_cap( 'ais_access_services' ),
			'ais_use_playground'  => $this->current_user->has_cap( 'ais_use_playground' ),
		);

		foreach ( $this->services_api->get_registered_service_slugs() as $slug ) {
			$capabilities[ "ais_access_service::{$slug}" ] = $this->current_user->has_cap( 'ais_access_service', $slug );
		}

		return $capabilities;
	}
}
