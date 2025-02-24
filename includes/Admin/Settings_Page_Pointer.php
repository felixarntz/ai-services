<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Admin\Settings_Page_Pointer
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Admin;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pointers\Abstract_Admin_Page_Link_Pointer;

/**
 * Class representing a WP Admin pointer to the plugin's admin settings page.
 *
 * @since n.e.x.t
 */
class Settings_Page_Pointer extends Abstract_Admin_Page_Link_Pointer {

	/**
	 * Renders the admin pointer content HTML.
	 *
	 * @since n.e.x.t
	 */
	public function render(): void {
		?>
		<h3><?php esc_html_e( 'Welcome to the WP Starter Plugin!', 'wp-starter-plugin' ); ?></h3>
		<p><?php esc_html_e( 'This plugin is a boilerplate to build a WordPress plugin using object-oriented programming.', 'wp-starter-plugin' ); ?></p>
		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s: settings page URL */
					__( 'To get started, <a href="%s">configure WP Starter Plugin</a>.', 'wp-starter-plugin' ),
					$this->admin_page_link->get_url()
				),
				array( 'a' => array( 'href' => array() ) )
			);
			?>
		</p>
		<?php
	}
}
