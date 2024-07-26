<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Generative_Model
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini;

use InvalidArgumentException;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Exception\Generative_AI_Exception;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Candidate;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Content;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Parts;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Safety_Setting;

/**
 * Class representing a generative model API.
 *
 * @since n.e.x.t
 */
class Generative_Model {

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
	 * @param array<string, mixed> $model_params    The model parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameter is missing.
	 */
	public function __construct( string $api_key, array $model_params, array $request_options = array() ) {
		$this->api_key         = $api_key;
		$this->api             = new Gemini_API( $this->api_key );
		$this->request_options = $request_options;

		if ( ! isset( $model_params['model'] ) ) {
			throw new InvalidArgumentException(
				esc_html__( 'The model parameter is required.', 'wp-oop-plugin-lib-example' )
			);
		}

		if ( str_contains( $model_params['model'], '/' ) ) {
			$this->model = $model_params['model'];
		} else {
			$this->model = 'models/' . $model_params['model'];
		}

		$this->generation_config = $model_params['generation_config'] ?? array();

		if ( isset( $model_params['safety_settings'] ) ) {
			foreach ( $model_params['safety_settings'] as $safety_setting ) {
				if ( ! $safety_setting instanceof Safety_Setting ) {
					throw new InvalidArgumentException(
						esc_html__( 'The safety_settings parameter must contain Safety_Setting instances.', 'wp-oop-plugin-lib-example' )
					);
				}
			}
			$this->safety_settings = $model_params['safety_settings'];
		} else {
			$this->safety_settings = array();
		}

		// TODO: Add support for tools and tool config, to support code generation.

		if ( isset( $model_params['system_instruction'] ) ) {
			$this->system_instruction = Formatter::format_system_instruction( $model_params['system_instruction'] );
		}
	}

	/**
	 * Generates content using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param string|Parts|Content|Content[] $content          The content to generate.
	 * @param array<string, mixed>           $request_options Optional. The request options. Default empty array.
	 * @return Candidate[] The response candidates with generated content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	public function generate_content( $content, array $request_options = array() ): array {
		if ( is_array( $content ) ) {
			$content = array_map(
				array( Formatter::class, 'format_new_content' ),
				$content
			);
		} else {
			$content = array( Formatter::format_new_content( $content ) );
		}

		$params = array(
			'contents'         => array_map(
				static function ( Content $content ) {
					return $content->to_array();
				},
				$content
			),
			'generationConfig' => $this->generation_config,
			'safetySettings'   => array_map(
				static function ( Safety_Setting $safety_setting ) {
					return $safety_setting->to_array();
				},
				$this->safety_settings
			),
		);
		if ( $this->system_instruction ) {
			$params['systemInstruction'] = $this->system_instruction->to_array();
		}

		$response = $this->api->generate_content(
			$this->model,
			array_filter( $params ),
			array_merge(
				$this->request_options,
				$request_options
			)
		);

		if ( ! isset( $response['candidates'] ) || ! $response['candidates'] ) {
			throw new Generative_AI_Exception(
				esc_html__( 'The response from the AI service is missing the "candidates" key.', 'wp-oop-plugin-lib-example' )
			);
		}

		return array_map(
			array( Candidate::class, 'from_array' ),
			$response['candidates']
		);
	}

	/**
	 * Starts a multi-turn chat session using the model.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $chat_params Optional. The chat parameters. Default empty array.
	 * @return Chat_Session The chat session.
	 */
	public function start_chat( $chat_params = array() ): Chat_Session {
		$chat_params = array_merge(
			array(
				// TODO: Add support for tools and tool config, to support code generation.
				'model'              => $this->model,
				'generation_config'  => $this->generation_config,
				'safety_settings'    => $this->safety_settings,
				'system_instruction' => $this->system_instruction,
			),
			$chat_params
		);

		return new Chat_Session(
			$this->api_key,
			array_filter( $chat_params ),
			$this->request_options
		);
	}
}
