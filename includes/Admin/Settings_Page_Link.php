<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Admin\Settings_Page_Link
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Admin;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Links\Admin_Page_Link;

/**
 * Class representing a link to the plugin's admin settings page.
 *
 * @since n.e.x.t
 */
class Settings_Page_Link extends Admin_Page_Link {

	/**
	 * Gets the admin link label.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The admin link label.
	 */
	public function get_label(): string {
		return __( 'Settings', 'wp-starter-plugin' );
	}
}
