<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Admin\Playground_Page
 *
 * @since 0.4.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Admin;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Abstract_Admin_Page;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;

/**
 * Class representing the plugin's AI playground page.
 *
 * @since 0.4.0
 */
class Playground_Page extends Abstract_Admin_Page {

	/**
	 * WordPress script registry.
	 *
	 * @since 0.4.0
	 * @var Script_Registry
	 */
	private $script_registry;

	/**
	 * WordPress style registry.
	 *
	 * @since 0.4.0
	 * @var Style_Registry
	 */
	private $style_registry;

	/**
	 * Constructor.
	 *
	 * @since 0.4.0
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
	 * @since 0.4.0
	 */
	public function load(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				// The Playground script implicitly requires the media library.
				wp_enqueue_media();

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
	 * @since 0.4.0
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
	 * @since 0.4.0
	 *
	 * @return string Admin page slug.
	 */
	protected function slug(): string {
		return 'ais_playground';
	}

	/**
	 * Returns the admin page title.
	 *
	 * @since 0.4.0
	 *
	 * @return string Admin page title.
	 */
	protected function title(): string {
		return __( 'AI Playground', 'ai-services' );
	}

	/**
	 * Returns the admin page's required capability.
	 *
	 * @since 0.4.0
	 *
	 * @return string Admin page capability.
	 */
	protected function capability(): string {
		return 'ais_use_playground';
	}

	/**
	 * Preloads relevant REST API data for the settings page so that it is available immediately.
	 *
	 * @since 0.4.0
	 */
	private function preload_rest_api_data(): void {
		$preload_paths = array(
			'/ai-services/v1/services',
			'/ai-services/v1/features/ai-playground/histories/default',
		);

		$preload_data = array_reduce(
			$preload_paths,
			'rest_preload_api_request',
			array()
		);

		/*
		 * If no history is currently saved, the endpoint will (rightfully) return a 404.
		 * This leads to the preload data being empty, which in turn causes the API fetch middleware to not work.
		 * To work around this, we manually add an empty history to the preload data.
		 */
		if ( ! isset( $preload_data['/ai-services/v1/features/ai-playground/histories/default'] ) ) {
			$preload_data['/ai-services/v1/features/ai-playground/histories/default'] = array(
				'body'    => array(
					'feature'     => 'ai-playground',
					'slug'        => 'default',
					'lastUpdated' => '',
					'entries'     => array(),
				),
				'headers' => array(),
			);
		}

		$this->script_registry->add_inline_code(
			'wp-api-fetch',
			sprintf(
				'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );',
				wp_json_encode( $preload_data )
			)
		);
	}
}
