<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Admin\Playground_Page_Pointer
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Admin;

use Felix_Arntz\AI_Services\Services\Services_API;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Links\Admin_Page_Link;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Admin_Pointers\Abstract_Admin_Page_Link_Pointer;

/**
 * Class representing a WP Admin pointer to the plugin's AI Playground page.
 *
 * @since n.e.x.t
 */
class Playground_Page_Pointer extends Abstract_Admin_Page_Link_Pointer {

	/**
	 * Services API instance.
	 *
	 * @since n.e.x.t
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Admin_Page_Link $admin_page_link The admin page link instance.
	 * @param Services_API    $services_api    The services API instance.
	 */
	public function __construct( Admin_Page_Link $admin_page_link, Services_API $services_api ) {
		parent::__construct( $admin_page_link );

		$this->services_api = $services_api;
	}


	/**
	 * Renders the admin pointer content HTML.
	 *
	 * @since n.e.x.t
	 */
	public function render(): void {
		?>
		<h3><?php esc_html_e( 'Explore the AI Playground!', 'ai-services' ); ?></h3>
		<p><?php esc_html_e( 'The AI Playground allows you to explore all available AI services and models and their capabilities through a flexible user interface.', 'ai-services' ); ?></p>
		<p><?php esc_html_e( 'It lets you generate content such as text and images, and explore advanced capabilities like AI function calling.', 'ai-services' ); ?></p>
		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s: AI Playground page URL */
					__( '<a href="%s">Visit the AI Playground</a> to send your first prompts.', 'ai-services' ),
					$this->admin_page_link->get_url()
				),
				array( 'a' => array( 'href' => array() ) )
			);
			?>
		</p>
		<?php
	}

	/**
	 * Checks whether the pointer should be displayed on the current screen.
	 *
	 * The method should not check whether anything related to the current user, such as whether they have the required
	 * capability or whether they have already dismissed the pointer. This is handled separately.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $hook_suffix The current admin screen hook suffix.
	 * @return bool True if the pointer is active, false otherwise.
	 */
	public function is_active( string $hook_suffix ): bool {
		// Also show pointer on the AI Services settings page when relevant.
		$show_on_page = parent::is_active( $hook_suffix );
		if ( ! $show_on_page && 'settings_page_ais_services' !== $hook_suffix ) {
			return false;
		}

		// Only show pointer if services are configured.
		return $this->services_api->has_available_services();
	}
}
