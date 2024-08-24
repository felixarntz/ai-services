<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Services\Dependencies\Services_Script_Style_Loader
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Services\Dependencies;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;

/**
 * Class responsible for registering the available AI service related scripts and styles.
 *
 * @since n.e.x.t
 */
class Services_Script_Style_Loader {

	/**
	 * The plugin environment.
	 *
	 * @since n.e.x.t
	 * @var Plugin_Env
	 */
	private $plugin_env;

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
	 * @param Plugin_Env      $plugin_env      The plugin environment.
	 * @param Script_Registry $script_registry WordPress script registry.
	 * @param Style_Registry  $style_registry  WordPress style registry.
	 */
	public function __construct( Plugin_Env $plugin_env, Script_Registry $script_registry, Style_Registry $style_registry ) {
		$this->plugin_env      = $plugin_env;
		$this->script_registry = $script_registry;
		$this->style_registry  = $style_registry;
	}

	/**
	 * Registers the plugin's available scripts and styles.
	 *
	 * @since n.e.x.t
	 */
	public function register_scripts_and_styles(): void {
		$this->script_registry->register(
			'wpsp-components',
			array(
				'src'      => $this->plugin_env->url( 'build/components/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/components/index.asset.php' ),
				'strategy' => 'defer',
			)
		);

		$this->script_registry->register(
			'wpsp-settings-store',
			array(
				'src'      => $this->plugin_env->url( 'build/settings-store/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/settings-store/index.asset.php' ),
				'strategy' => 'defer',
			)
		);

		$this->script_registry->register(
			'wpsp-services-page',
			array(
				'src'      => $this->plugin_env->url( 'build/services-page/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/services-page/index.asset.php' ),
				'strategy' => 'defer',
			)
		);

		$this->style_registry->register(
			'wpsp-components',
			array(
				'src'          => $this->plugin_env->url( 'build/components/style-index.css' ),
				'path'         => $this->plugin_env->path( 'build/components/style-index.css' ),
				'manifest'     => $this->plugin_env->path( 'build/components/index.asset.php' ),
				'dependencies' => array( 'wp-components', 'wp-editor' ),
			)
		);

		$this->style_registry->register(
			'wpsp-services-page',
			array(
				'src'          => $this->plugin_env->url( 'build/services-page/style-index.css' ),
				'path'         => $this->plugin_env->path( 'build/services-page/style-index.css' ),
				'manifest'     => $this->plugin_env->path( 'build/services-page/index.asset.php' ),
				'dependencies' => array( 'wp-components', 'wpsp-components' ),
			)
		);
	}
}
