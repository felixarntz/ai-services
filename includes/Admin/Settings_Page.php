<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Admin\Settings_Page
 *
 * @since n.e.x.t
 * @package wp-oop-plugin-lib-example
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Admin;

use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pages\Abstract_Admin_Page;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Vendor_NS\WP_OOP_Plugin_Lib_Example_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;

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
				$this->script_registry->enqueue( 'wpoopple-settings-page' );
				$this->style_registry->enqueue( 'wpoopple-settings-page' );
			}
		);

		add_filter(
			'admin_body_class',
			static function ( $classes ) {
				return "$classes remove-screen-spacing";
			}
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
			<?php esc_html_e( 'Loading…', 'wp-oop-plugin-lib-example' ); ?>
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
		return 'wpoopple-settings';
	}

	/**
	 * Returns the admin page title.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page title.
	 */
	protected function title(): string {
		return __( 'WP OOP Plugin Lib Example', 'wp-oop-plugin-lib-example' );
	}

	/**
	 * Returns the admin page's required capability.
	 *
	 * @since n.e.x.t
	 *
	 * @return string Admin page capability.
	 */
	protected function capability(): string {
		return 'manage_options';
	}
}
