<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Admin\Settings_Page_Link
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Admin;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Links\Admin_Page_Link;

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
		return __( 'Settings', 'ai-services' );
	}
}
