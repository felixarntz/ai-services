<?php
/**
 * Class Vendor_NS\WP_Starter_Plugin\Google\Google_AI_Model
 *
 * @since n.e.x.t
 * @package wp-plugin-starter
 */

namespace Vendor_NS\WP_Starter_Plugin\Google;

use InvalidArgumentException;
use Vendor_NS\WP_Starter_Plugin\Google\Types\Safety_Setting;
use Vendor_NS\WP_Starter_Plugin\Services\Abstract_Generative_AI_Model;
use Vendor_NS\WP_Starter_Plugin\Services\Exception\Generative_AI_Exception;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Candidates;
use Vendor_NS\WP_Starter_Plugin\Services\Types\Content;
use Vendor_NS\WP_Starter_Plugin\Services\Util\Formatter;

/**
 * Class representing a Google AI model.
 *
 * @since n.e.x.t
 */
class Google_AI_Model extends Abstract_Generative_AI_Model {

	/**
	 * The Google AI API instance.
	 *
	 * @since n.e.x.t
	 * @var Google_AI_API_Client
	 */
	private $api;

	/**
	 * The model slug.
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
	 * @param Google_AI_API_Client $api             The Google AI API instance.
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameter is missing.
	 */
	public function __construct( Google_AI_API_Client $api, string $model, array $model_params = array(), array $request_options = array() ) {
		$this->api             = $api;
		$this->request_options = $request_options;

		if ( str_contains( $model, '/' ) ) {
			$this->model = $model;
		} else {
			$this->model = 'models/' . $model;
		}

		$this->generation_config = $model_params['generation_config'] ?? array();

		if ( isset( $model_params['safety_settings'] ) ) {
			foreach ( $model_params['safety_settings'] as $index => $safety_setting ) {
				if ( is_array( $safety_setting ) ) {
					$model_params['safety_settings'][ $index ] = Safety_Setting::from_array( $safety_setting );
				} elseif ( ! $safety_setting instanceof Safety_Setting ) {
					throw new InvalidArgumentException(
						esc_html__( 'The safety_settings parameter must contain Safety_Setting instances.', 'wp-starter-plugin' )
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
	 * Gets the model slug.
	 *
	 * @since n.e.x.t
	 *
	 * @return string The model slug.
	 */
	public function get_model_slug(): string {
		if ( str_starts_with( $this->model, 'models/' ) ) {
			return substr( $this->model, 7 );
		}
		return $this->model;
	}

	/**
	 * Sends a request to generate text content.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_generate_text_request( array $contents, array $request_options ): Candidates {
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

		$request  = $this->api->create_generate_content_request(
			$this->model,
			array_filter( $params ),
			array_merge(
				$this->request_options,
				$request_options
			)
		);
		$response = $this->api->make_request( $request );

		return $this->get_response_candidates( $response );
	}

	/**
	 * Extracts the candidates with content from the response.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $response The response data.
	 * @return Candidates The candidates with content parts.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not have any candidates with content.
	 */
	private function get_response_candidates( array $response ): Candidates {
		if ( ! isset( $response['candidates'] ) || ! $response['candidates'] ) {
			throw new Generative_AI_Exception(
				esc_html__( 'The response from the Google AI API is missing the "candidates" key.', 'wp-starter-plugin' )
			);
		}

		$candidates = array();
		$errors     = array();
		foreach ( $response['candidates'] as $candidate ) {
			if ( ! isset( $candidate['content'] ) ) {
				if ( isset( $candidate['finishReason'] ) ) {
					$errors[] = $candidate['finishReason'];
				}
				continue;
			}

			$candidates[] = $candidate;
		}

		if ( count( $candidates ) === 0 ) {
			$message = __( 'The response from the Google AI API does not include any candidates with content.', 'wp-starter-plugin' );

			$errors = array_unique( $errors );
			if ( count( $errors ) > 0 ) {
				$message .= ' ' . sprintf(
					/* translators: %s: finish reason code */
					__( 'Finish reason: %s', 'wp-starter-plugin' ),
					implode(
						_x( ', ', 'Used between list items, there is a space after the comma.', 'wp-starter-plugin' ),
						$errors
					)
				);
			}
			throw new Generative_AI_Exception(
				esc_html( $message )
			);
		}

		return Candidates::from_array( $candidates );
	}
}
