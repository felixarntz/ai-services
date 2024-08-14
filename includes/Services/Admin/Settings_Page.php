<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\Admin\Settings_Page
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Admin;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Abstract_Admin_Page;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;

/**
 * Class representing the plugin's admin settings page.
 *
 * @since n.e.x.t
 */
class Settings_Page extends Abstract_Admin_Page {

	/**
	 * WordPress script registry.
	 *
	 * @since n.e.x.t
	 * @var Script_Registry
	 */
	private $script_registry;

	/**
	 * WordPress style registry.
	 *
	 * @since n.e.x.t
	 * @var Style_Registry
	 */
	private $style_registry;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Script_Registry $script_registry WordPress script registry.
	 * @param Style_Registry  $style_registry  WordPress style registry.
	 */
	public function __construct( Script_Registry $script_registry, Style_Registry $style_registry ) {
		parent::__construct();

		$this->script_registry = $script_registry;
		$this->style_registry  = $style_registry;
	}

	/**
	 * Initializes functionality for the admin page.
	 *
	 * @since n.e.x.t
	 */
	public function load(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				$this->script_registry->register(
					'wpsp_services',
					array(
						'src'      => $this->plugin_env->url( 'build/index.js' ),
						'manifest' => $this->plugin_env->path( 'build/index.asset.php' ),
						'strategy' => 'defer',
					)
				);

				$this->script_registry->enqueue( 'wpsp_services' );
				$this->script_registry->enqueue( 'wpsp-settings-page' );
				$this->style_registry->enqueue( 'wpsp-settings-page' );

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
	 * @since n.e.x.t
	 */
	public function render(): void {
		?>
		<div id="settings-page-root" class="wrap">
			<?php esc_html_e( 'Loading…', 'wp-starter-plugin' ); ?>
		</div>
		<?php
	}

	/**
	 * Returns the admin page slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page slug.
	 */
	protected function slug(): string {
		return 'wpsp_services';
	}

	/**
	 * Returns the admin page title.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page title.
	 */
	protected function title(): string {
		return __( 'AI Services', 'wp-starter-plugin' );
	}

	/**
	 * Returns the admin page's required capability.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page capability.
	 */
	protected function capability(): string {
		return 'wpsp_manage_services';
	}

	/**
	 * Preloads relevant REST API data for the settings page so that it is available immediately.
	 *
	 * @since n.e.x.t
	 */
	private function preload_rest_api_data(): void {
		$preload_paths = array( '/wp/v2/settings' );

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
}
