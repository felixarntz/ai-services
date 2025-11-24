<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Dependencies\Services_Script_Style_Loader
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Dependencies;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;

/**
 * Class responsible for registering the available AI service related scripts and styles.
 *
 * @since 0.1.0
 */
class Services_Script_Style_Loader {

	/**
	 * The plugin environment.
	 *
	 * @since 0.1.0
	 * @var Plugin_Env
	 */
	private $plugin_env;

	/**
	 * WordPress script registry.
	 *
	 * @since 0.1.0
	 * @var Script_Registry
	 */
	private $script_registry;

	/**
	 * WordPress style registry.
	 *
	 * @since 0.1.0
	 * @var Style_Registry
	 */
	private $style_registry;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function register_scripts_and_styles(): void {
		/**
		 * Filters whether to skip API requests for all AI services.
		 *
		 * This effectively enables a "client-side only" mode for the AI Services plugin,
		 * where only client-side models on the user's device are used.
		 *
		 * @since 0.7.0
		 *
		 * @param bool $skip_api_request Whether to skip API requests for all AI services.
		 *                               By default, this is true if the user is not logged in.
		 */
		$skip_api_request = (bool) apply_filters( 'ai_services_skip_api_request', ! is_user_logged_in() );

		$this->script_registry->register(
			'ais-ai',
			array(
				'src'      => $this->plugin_env->url( 'build/ai/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/ai/index.asset.php' ),
				'strategy' => 'defer',
			)
		);
		if ( $skip_api_request ) {
			$this->script_registry->add_inline_code(
				'ais-ai',
				'window.AI_SERVICES_SKIP_API_REQUEST = true;'
			);
		}

		$this->script_registry->register(
			'ais-settings',
			array(
				'src'      => $this->plugin_env->url( 'build/settings/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/settings/index.asset.php' ),
				'strategy' => 'defer',
			)
		);

		$this->script_registry->register(
			'ais-components',
			array(
				'src'      => $this->plugin_env->url( 'build/components/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/components/index.asset.php' ),
				'strategy' => 'defer',
			)
		);

		$this->script_registry->register(
			'ais-services-page',
			array(
				'src'      => $this->plugin_env->url( 'build/services-page/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/services-page/index.asset.php' ),
				'strategy' => 'defer',
			)
		);

		$this->script_registry->register(
			'ais-playground-page',
			array(
				'src'      => $this->plugin_env->url( 'build/playground-page/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/playground-page/index.asset.php' ),
				'strategy' => 'defer',
			)
		);

		$this->style_registry->register(
			'ais-wp-admin-components',
			array(
				'src'          => $this->plugin_env->url( 'build/wp-admin-components/style.css' ),
				'path'         => $this->plugin_env->path( 'build/wp-admin-components/style.css' ),
				'dependencies' => array( 'wp-components' ),
				'version'      => '1.0.0',
			)
		);

		$this->style_registry->register(
			'ais-wp-interface',
			array(
				'src'          => $this->plugin_env->url( 'build/wp-interface/style.css' ),
				'path'         => $this->plugin_env->path( 'build/wp-interface/style.css' ),
				'dependencies' => array( 'wp-components', 'wp-editor' ),
				'version'      => '1.0.0',
			)
		);

		$this->style_registry->register(
			'ais-components',
			array(
				'src'          => $this->plugin_env->url( 'build/components/style-index.css' ),
				'path'         => $this->plugin_env->path( 'build/components/style-index.css' ),
				'manifest'     => $this->plugin_env->path( 'build/components/index.asset.php' ),
				'dependencies' => array( 'wp-components', 'ais-wp-admin-components' ),
			)
		);

		$this->style_registry->register(
			'ais-services-page',
			array(
				'src'          => $this->plugin_env->url( 'build/services-page/style-index.css' ),
				'path'         => $this->plugin_env->path( 'build/services-page/style-index.css' ),
				'manifest'     => $this->plugin_env->path( 'build/services-page/index.asset.php' ),
				'dependencies' => array( 'wp-components', 'ais-components', 'ais-wp-interface' ),
			)
		);

		$this->style_registry->register(
			'ais-playground-page',
			array(
				'src'          => $this->plugin_env->url( 'build/playground-page/style-index.css' ),
				'path'         => $this->plugin_env->path( 'build/playground-page/style-index.css' ),
				'manifest'     => $this->plugin_env->path( 'build/playground-page/index.asset.php' ),
				'dependencies' => array( 'wp-components', 'ais-components', 'ais-wp-interface' ),
			)
		);
	}
}
