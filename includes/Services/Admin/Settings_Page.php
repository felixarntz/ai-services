<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Admin\Settings_Page
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Admin;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Abstract_Admin_Page;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;

/**
 * Class representing the plugin's admin settings page.
 *
 * @since 0.1.0
 */
class Settings_Page extends Abstract_Admin_Page {

	/**
	 * WordPress script registry.
	 *
	 * @since 0.1.0
	 * @var Script_Registry
	 */
	private $script_registry;

	/**
	 * WordPress style registry.
	 *
	 * @since 0.1.0
	 * @var Style_Registry
	 */
	private $style_registry;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Script_Registry $script_registry WordPress script registry.
	 * @param Style_Registry  $style_registry  WordPress style registry.
	 */
	public function __construct( Script_Registry $script_registry, Style_Registry $style_registry ) {
		parent::__construct();

		add_filter( 'plugin_action_links_' . AI_SERVICES_MAIN_FILE, array( $this, 'add_settings_action_link' ) );

		$this->script_registry = $script_registry;
		$this->style_registry  = $style_registry;
	}

	/**
	 * Initializes functionality for the admin page.
	 *
	 * @since 0.1.0
	 */
	public function load(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				$this->script_registry->enqueue( 'ais-services-page' );
				$this->style_registry->enqueue( 'ais-services-page' );

				$this->preload_rest_api_data();
			}
		);

		add_filter(
			'admin_body_class',
			static function ( $classes ) {
				return "$classes remove-screen-spacing";
			}
		);

		add_action(
			'admin_notices',
			static function () {
				remove_all_actions( 'admin_notices' );
			},
			-9999
		);
	}

	/**
	 * Renders the admin page.
	 *
	 * @since 0.1.0
	 */
	public function render(): void {
		?>
		<div id="settings-page-root" class="wrap">
			<?php esc_html_e( 'Loading…', 'ai-services' ); ?>
		</div>
		<?php
	}

	/**
	 * Returns the admin page slug.
	 *
	 * @since 0.1.0
	 *
	 * @return string Admin page slug.
	 */
	protected function slug(): string {
		return 'ais_services';
	}

	/**
	 * Returns the admin page title.
	 *
	 * @since 0.1.0
	 *
	 * @return string Admin page title.
	 */
	protected function title(): string {
		return __( 'AI Services', 'ai-services' );
	}

	/**
	 * Returns the admin page's required capability.
	 *
	 * @since 0.1.0
	 *
	 * @return string Admin page capability.
	 */
	protected function capability(): string {
		return 'ais_manage_services';
	}

	/**
	 * Preloads relevant REST API data for the settings page so that it is available immediately.
	 *
	 * @since 0.1.0
	 */
	private function preload_rest_api_data(): void {
		$preload_paths = array(
			'/ai-services/v1/services?context=edit',
			'/wp/v2/settings',
		);

		$preload_data = array_reduce(
			$preload_paths,
			'rest_preload_api_request',
			array()
		);

		$this->script_registry->add_inline_code(
			'wp-api-fetch',
			sprintf(
				'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );',
				wp_json_encode( $preload_data )
			)
		);
	}

	/**
	 * Adds a settings link to the plugin's action links.
	 *
	 * @since n.e.x.t
	 *
	 * @param string[]|mixed $links An array of plugin action links.
	 * @return string[]|mixed The modified list of actions.
	 */
	function add_settings_action_link( $links ) {
		if ( ! is_array( $links ) ) {
			return $links;
		}

		$settings_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( add_query_arg( 'page', $this->get_slug(), admin_url( 'options-general.php' ) ) ),
			esc_html__( 'Settings', 'ai-services' )
		);

		return array_merge(
			array( 'settings' => $settings_link ),
			$links
		);
	}
}
