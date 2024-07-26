<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Chat_Session
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini;

use InvalidArgumentException;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Content;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Parts;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Safety_Setting;

/**
 * Class representing a chat session with a generative model.
 *
 * @since n.e.x.t
 */
class Chat_Session {

	/**
	 * The Gemini API instance.
	 *
	 * @since n.e.x.t
	 * @var Gemini_API
	 */
	private $api;

	/**
	 * The Gemini API key.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $api_key;

	/**
	 * The model name.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $model;

	/**
	 * The generation configuration.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $generation_config;

	/**
	 * The safety settings.
	 *
	 * @since n.e.x.t
	 * @var Safety_Setting[]
	 */
	private $safety_settings;

	/**
	 * The system instruction.
	 *
	 * @since n.e.x.t
	 * @var Content|null
	 */
	private $system_instruction;

	/**
	 * The chat history.
	 *
	 * @since n.e.x.t
	 * @var Content[]
	 */
	private $history;

	/**
	 * The request options.
	 *
	 * @since n.e.x.t
	 * @var array<string, mixed>
	 */
	private $request_options;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param string               $api_key         The API key.
	 * @param array<string, mixed> $chat_params     The chat parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameter is missing.
	 */
	public function __construct( string $api_key, array $chat_params, array $request_options = array() ) {
		$this->api_key         = $api_key;
		$this->api             = new Gemini_API( $this->api_key );
		$this->request_options = $request_options;

		if ( ! isset( $chat_params['model'] ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'The model parameter is required.', 'wp-oop-plugin-lib-example' )
			);
		}

		if ( str_contains( $chat_params['model'], '/' ) ) {
			$this->model = $chat_params['model'];
		} else {
			$this->model = 'models/' . $chat_params['model'];
		}

		$this->generation_config = $chat_params['generation_config'] ?? array();

		if ( isset( $chat_params['safety_settings'] ) ) {
			foreach ( $chat_params['safety_settings'] as $safety_setting ) {
				if ( ! $safety_setting instanceof Safety_Setting ) {
					throw new InvalidArgumentException(
						esc_html__( 'The safety_settings parameter must contain Safety_Setting instances.', 'wp-oop-plugin-lib-example' )
					);
				}
			}
			$this->safety_settings = $chat_params['safety_settings'];
		} else {
			$this->safety_settings = array();
		}

		// TODO: Add support for tools and tool config, to support code generation.

		if ( isset( $chat_params['system_instruction'] ) ) {
			$this->system_instruction = Formatter::format_system_instruction( $model_params['system_instruction'] );
		}

		if ( isset( $chat_params['history'] ) ) {
			$this->validate_history( $chat_params['history'] );
			$this->history = $chat_params['history'];
		} else {
			$this->history = array();
		}
	}

	/**
	 * Validates the chat history.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $history The chat history.
	 *
	 * @throws InvalidArgumentException Thrown if the history is invalid.
	 */
	private function validate_history( array $history ): void {
		$first = true;
		foreach ( $history as $content ) {
			if ( ! $content instanceof Content ) {
				throw new InvalidArgumentException(
					esc_html__( 'The history must contain Content instances.', 'wp-oop-plugin-lib-example' )
				);
			}

			if ( $first && 'user' !== $content->get_role() ) {
				throw new InvalidArgumentException(
					esc_html__( 'The first Content instance in the history must be user content.', 'wp-oop-plugin-lib-example' )
				);
			}

			if ( ! $this->is_valid_role( $content->get_role() ) ) {
				throw new InvalidArgumentException(
					esc_html(
						sprintf(
							/* translators: %s: invalid role encountered */
							__( 'The role %s is invalid.', 'wp-oop-plugin-lib-example' ),
							$content->get_role()
						)
					)
				);
			}

			if ( $content->get_parts()->count() < 1 ) {
				throw new InvalidArgumentException(
					esc_html__( 'Each Content instance must have at least one part.', 'wp-oop-plugin-lib-example' )
				);
			}

			$first = false;
		}
	}

	/**
	 * Checks if the given role is valid.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $role The role to check.
	 * @return bool True if the role is valid, false otherwise.
	 */
	private function is_valid_role( string $role ): bool {
		return in_array( $role, array( 'user', 'model', 'function', 'system' ), true );
	}
}
