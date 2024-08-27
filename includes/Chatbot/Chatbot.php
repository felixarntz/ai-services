<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Chatbot\Chatbot
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Chatbot;

use Vendor_NS\WP_Starter_Plugin\Services\Contracts\Generative_AI_Service;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Style_Registry;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\With_Hooks;
use Vendor_NS\WP_Starter_Plugin_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Plugin_Env;

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
	 * @param Script_Registry $script_registry The WordPress script registry instance.
	 * @param Style_Registry  $style_registry  The WordPress style registry instance.
	 */
	public function __construct( Plugin_Env $plugin_env, Script_Registry $script_registry, Style_Registry $style_registry ) {
		$this->plugin_env      = $plugin_env;
		$this->script_registry = $script_registry;
		$this->style_registry  = $style_registry;
	}

	/**
	 * Sets the AI service for the chatbot.
	 *
	 * @since n.e.x.t
	 *
	 * @param Generative_AI_Service $ai_service The AI service.
	 */
	public function set_service( Generative_AI_Service $ai_service ): void {
		$this->ai = new Chatbot_AI( $ai_service );
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
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		// Testing.
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-info"><p>';
				$model = $this->ai->get_model();
				try {
					$candidates = $model->generate_content( 'Where can I add new pages?' );
					$text       = $this->ai->get_text_from_candidates( $candidates );
					var_dump( $text ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
				} catch ( \Exception $e ) {
					echo 'An error occurred: ';
					echo esc_html( $e->getMessage() );
				}
				echo '</p></div>';
			}
		);
	}

	/**
	 * Registers the assets needed for the chatbot.
	 *
	 * @since n.e.x.t
	 */
	public function register_assets(): void {
		$this->script_registry->register(
			'wpsp_chatbot',
			array(
				'src'      => $this->plugin_env->url( 'build/chatbot/index.js' ),
				'manifest' => $this->plugin_env->path( 'build/chatbot/index.asset.php' ),
				'strategy' => 'defer',
			)
		);
		$this->style_registry->register(
			'wpsp_chatbot',
			array(
				'src'          => $this->plugin_env->url( 'build/chatbot/style-index.css' ),
				'path'         => $this->plugin_env->path( 'build/chatbot/style-index.css' ),
				'manifest'     => $this->plugin_env->path( 'build/chatbot/index.asset.php' ),
				'dependencies' => array(),
			)
		);
	}

	/**
	 * Enqueues the assets needed for the chatbot.
	 *
	 * @since n.e.x.t
	 */
	public function enqueue_assets(): void {
		$this->script_registry->enqueue( 'wpsp_chatbot' );
		$this->style_registry->enqueue( 'wpsp_chatbot' );
	}
}
