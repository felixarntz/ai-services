<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Admin\Playground_Page
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Admin;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Abstract_Admin_Page;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;

/**
 * Class representing the plugin's AI playground page.
 *
 * @since n.e.x.t
 */
class Playground_Page extends Abstract_Admin_Page {

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
				$this->script_registry->enqueue( 'ais-playground-page' );
				$this->style_registry->enqueue( 'ais-playground-page' );

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
		<div id="playground-page-root" class="wrap">
			<?php esc_html_e( 'Loading…', 'ai-services' ); ?>
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
		return 'ais_playground';
	}

	/**
	 * Returns the admin page title.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page title.
	 */
	protected function title(): string {
		return __( 'AI Playground', 'ai-services' );
	}

	/**
	 * Returns the admin page's required capability.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page capability.
	 */
	protected function capability(): string {
		return 'ais_use_playground';
	}

	/**
	 * Preloads relevant REST API data for the settings page so that it is available immediately.
	 *
	 * @since n.e.x.t
	 */
	private function preload_rest_api_data(): void {
		$preload_paths = array(
			'/ai-services/v1/services',
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
}
