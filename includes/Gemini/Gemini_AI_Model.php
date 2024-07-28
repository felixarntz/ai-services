<?php
/**
 * Class Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Gemini_AI_Model
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini;

use InvalidArgumentException;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Gemini\Types\Safety_Setting;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Abstract_Generative_AI_Model;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Candidate;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Types\Content;
use Vendor_NS\WP_OOP_Plugin_Lib_Example\Services\Util\Formatter;

/**
 * Class representing a Gemini AI model.
 *
 * @since n.e.x.t
 */
class Gemini_AI_Model extends Abstract_Generative_AI_Model {

	/**
	 * The Gemini API instance.
	 *
	 * @since n.e.x.t
	 * @var Gemini_API
	 */
	private $api;

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
	 * @param Gemini_API           $api             The Gemini API instance.
	 * @param array<string, mixed> $model_params    The model parameters.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameter is missing.
	 */
	public function __construct( Gemini_API $api, array $model_params, array $request_options = array() ) {
		$this->api             = $api;
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
	 * Sends a request to generate content.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidate[] The response candidates with generated content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_generate_content_request( array $contents, array $request_options ): array {
		$params = array(
			// TODO: Add support for tools and tool config, to support code generation.
			'contents'         => array_map(
				static function ( Content $content ) {
					return $content->to_array();
				},
				$contents
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
}
