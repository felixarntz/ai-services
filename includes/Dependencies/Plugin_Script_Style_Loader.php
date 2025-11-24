<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Dependencies\Plugin_Script_Style_Loader
 *
 * @since n.e.x.t
 * @package wp-starter-plugin
 */

namespace Vendor_NS\WP_Starter_Plugin\Dependencies;

use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;

/**
 * Class responsible for registering the plugin's available scripts and styles.
 *
 * @since n.e.x.t
 */
class Plugin_Script_Style_Loader {

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
			'wpsp-settings',
			array(
				'src'      => $this->plugin_env->url( 'build/settings/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/settings/index.asset.php' ),
				'strategy' => 'defer',
			)
		);

		$this->script_registry->register(
			'wpsp-settings-page',
			array(
				'src'      => $this->plugin_env->url( 'build/settings-page/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/settings-page/index.asset.php' ),
				'strategy' => 'defer',
			)
		);

		$this->style_registry->register(
			'wpsp-wp-interface',
			array(
				'src'          => $this->plugin_env->url( 'build/wp-interface/style.css' ),
				'path'         => $this->plugin_env->path( 'build/wp-interface/style.css' ),
				'dependencies' => array( 'wp-components', 'wp-editor' ),
				'version'      => '1.0.0',
			)
		);

		$this->style_registry->register(
			'wpsp-settings-page',
			array(
				'src'          => $this->plugin_env->url( 'build/settings-page/style-index.css' ),
				'path'         => $this->plugin_env->path( 'build/settings-page/style-index.css' ),
				'manifest'     => $this->plugin_env->path( 'build/settings-page/index.asset.php' ),
				'dependencies' => array( 'wp-components', 'wpsp-wp-interface' ),
			)
		);
	}
}
