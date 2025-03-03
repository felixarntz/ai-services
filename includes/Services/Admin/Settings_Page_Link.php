<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Admin\Settings_Page_Link
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Admin;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Links\Admin_Page_Link;

/**
 * Class representing a link to the plugin's admin settings page.
 *
 * @since 0.5.0
 */
class Settings_Page_Link extends Admin_Page_Link {

	/**
	 * Gets the admin link label.
	 *
	 * @since 0.5.0
	 *
	 * @return string The admin link label.
	 */
	public function get_label(): string {
		return __( 'Settings', 'ai-services' );
	}
}
