<?php
/**
 * Class Felix_Arntz\AI_Services\Chatbot\Chatbot
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Chatbot;

use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\With_Hooks;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Network_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Site_Env;

/**
 * Class controlling the AI-powered chatbot.
 *
 * @since n.e.x.t
 */
class Chatbot implements With_Hooks {

	/**
	 * The plugin environment.
	 *
	 * @since n.e.x.t
	 * @var Plugin_Env
	 */
	private $plugin_env;

	/**
	 * WordPress script registry instance.
	 *
	 * @since n.e.x.t
	 * @var Script_Registry
	 */
	private $script_registry;

	/**
	 * WordPress style registry instance.
	 *
	 * @since n.e.x.t
	 * @var Style_Registry
	 */
	private $style_registry;

	/**
	 * The AI instance.
	 *
	 * @since n.e.x.t
	 * @var Chatbot_AI
	 */
	private $ai;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param Plugin_Env      $plugin_env      The plugin environment.
	 * @param Site_Env        $site_env        The site environment.
	 * @param Network_Env     $network_env     The network environment.
	 * @param Current_User    $current_user    The current user instance.
	 * @param Script_Registry $script_registry The WordPress script registry instance.
	 * @param Style_Registry  $style_registry  The WordPress style registry instance.
	 */
	public function __construct(
		Plugin_Env $plugin_env,
		Site_Env $site_env,
		Network_Env $network_env,
		Current_User $current_user,
		Script_Registry $script_registry,
		Style_Registry $style_registry
	) {
		$this->plugin_env      = $plugin_env;
		$this->script_registry = $script_registry;
		$this->style_registry  = $style_registry;
		$this->ai              = new Chatbot_AI( $site_env, $network_env, $current_user );
	}

	/**
	 * Adds relevant WordPress hooks.
	 *
	 * @since n.e.x.t
	 */
	public function add_hooks(): void {
		if ( doing_action( 'init' ) ) {
			$this->register_assets();
		} else {
			add_action( 'init', array( $this, 'register_assets' ) );
		}

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'admin_footer', array( $this, 'render_app_root' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'wp_footer', array( $this, 'render_app_root' ) );
		}

		add_filter(
			'ai_services_rest_model_params',
			array( $this, 'filter_rest_model_params' )
		);
	}

	/**
	 * Gets the model parameters to use for the chatbot.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, mixed> The model parameters, containing 'system_instruction'.
	 */
	public function get_model_params(): array {
		return array(
			'system_instruction' => $this->ai->get_system_instruction(),
		);
	}

	/**
	 * Registers the assets needed for the chatbot.
	 *
	 * @since n.e.x.t
	 */
	public function register_assets(): void {
		$this->script_registry->register(
			'ais_chatbot',
			array(
				'src'      => $this->plugin_env->url( 'build/chatbot/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/chatbot/index.asset.php' ),
				'strategy' => 'defer',
			)
		);
		$this->style_registry->register(
			'react_chatbot_kit',
			array(
				'src'          => $this->plugin_env->url( 'build/chatbot/index.css' ),
				'path'         => $this->plugin_env->path( 'build/chatbot/index.css' ),
				'manifest'     => $this->plugin_env->path( 'build/chatbot/index.asset.php' ),
				'dependencies' => array(),
			)
		);
		$this->style_registry->register(
			'ais_chatbot',
			array(
				'src'          => $this->plugin_env->url( 'build/chatbot/style-index.css' ),
				'path'         => $this->plugin_env->path( 'build/chatbot/style-index.css' ),
				'manifest'     => $this->plugin_env->path( 'build/chatbot/index.asset.php' ),
				// Don't use 'wp-components' everywhere because it is too heavy to load for just a button.
				'dependencies' => array( 'react_chatbot_kit' ),
			)
		);
	}

	/**
	 * Enqueues the assets needed for the chatbot.
	 *
	 * @since n.e.x.t
	 */
	public function enqueue_assets(): void {
		$this->script_registry->enqueue( 'ais_chatbot' );
		$this->style_registry->enqueue( 'ais_chatbot' );
	}

	/**
	 * Renders the chatbot app root.
	 *
	 * @since n.e.x.t
	 */
	public function render_app_root(): void {
		?>
		<div id="ai-services-chatbot-root" class="chatbot-root"></div>
		<?php
	}

	/**
	 * Filters the model parameters for the REST API.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $model_params The model parameters. Commonly supports at least the parameters
	 *                                           'generation_config' and 'system_instruction'.
	 * @return array<string, mixed> The filtered model parameters.
	 */
	public function filter_rest_model_params( array $model_params ): array {
		if ( isset( $model_params['use_wpps_chatbot'] ) || isset( $model_params['useWppsChatbot'] ) ) {
			$model_params = array_merge( $model_params, $this->get_model_params() );
			unset( $model_params['use_wpps_chatbot'], $model_params['useWppsChatbot'] );
		}
		return $model_params;
	}
}
