<?php
/**
 * Class Felix_Arntz\AI_Services\Google\Google_AI_Model
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Google;

use Felix_Arntz\AI_Services\Google\Types\Safety_Setting;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Types\Candidates;
use Felix_Arntz\AI_Services\Services\Types\Content;
use Felix_Arntz\AI_Services\Services\Types\Generation_Config;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use InvalidArgumentException;

/**
 * Class representing a Google AI model.
 *
 * @since 0.1.0
 */
class Google_AI_Model implements Generative_AI_Model, With_Multimodal_Input, With_Text_Generation {
	use With_Text_Generation_Trait;

	/**
	 * The Google AI API instance.
	 *
	 * @since 0.1.0
	 * @var Google_AI_API_Client
	 */
	private $api;

	/**
	 * The model slug.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $model;

	/**
	 * The generation configuration.
	 *
	 * @since 0.1.0
	 * @var Generation_Config|null
	 */
	private $generation_config;

	/**
	 * The safety settings.
	 *
	 * @since 0.1.0
	 * @var Safety_Setting[]
	 */
	private $safety_settings;

	/**
	 * The system instruction.
	 *
	 * @since 0.1.0
	 * @var Content|null
	 */
	private $system_instruction;

	/**
	 * The request options.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	private $request_options;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Google_AI_API_Client $api             The Google AI API instance.
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. See
	 *                                              {@see Google_AI_Service::get_model()} for the list of available
	 *                                              parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Google_AI_API_Client $api, string $model, array $model_params = array(), array $request_options = array() ) {
		$this->api             = $api;
		$this->request_options = $request_options;

		if ( str_contains( $model, '/' ) ) {
			$this->model = $model;
		} else {
			$this->model = 'models/' . $model;
		}

		if ( isset( $model_params['generationConfig'] ) ) {
			if ( $model_params['generationConfig'] instanceof Generation_Config ) {
				$this->generation_config = $model_params['generationConfig'];
			} else {
				$this->generation_config = Generation_Config::from_array( $model_params['generationConfig'] );
			}
		}

		// TODO: Add support for tools and tool config, to support code generation.

		if ( isset( $model_params['systemInstruction'] ) ) {
			$this->system_instruction = Formatter::format_system_instruction( $model_params['systemInstruction'] );
		}

		if ( isset( $model_params['safetySettings'] ) ) {
			foreach ( $model_params['safetySettings'] as $index => $safety_setting ) {
				if ( is_array( $safety_setting ) ) {
					$model_params['safetySettings'][ $index ] = Safety_Setting::from_array( $safety_setting );
				} elseif ( ! $safety_setting instanceof Safety_Setting ) {
					throw new InvalidArgumentException(
						esc_html__( 'The safetySettings parameter must contain Safety_Setting instances.', 'ai-services' )
					);
				}
			}
			$this->safety_settings = $model_params['safetySettings'];
		} else {
			$this->safety_settings = array();
		}
	}

	/**
	 * Gets the model slug.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
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
			'contents'       => array_map(
				array( $this, 'prepare_content_for_api_request' ),
				$contents
			),
			'safetySettings' => array_map(
				static function ( Safety_Setting $safety_setting ) {
					return $safety_setting->to_array();
				},
				$this->safety_settings
			),
		);
		if ( $this->generation_config ) {
			$params['generationConfig'] = $this->generation_config->to_array();
		}
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
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $response The response data.
	 * @return Candidates The candidates with content parts.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not have any candidates with content.
	 */
	private function get_response_candidates( array $response ): Candidates {
		if ( ! isset( $response['candidates'] ) || ! $response['candidates'] ) {
			throw new Generative_AI_Exception(
				esc_html(
					sprintf(
						/* translators: %s: key name */
						__( 'The response from the Google AI API is missing the "%s" key.', 'ai-services' ),
						'candidates'
					)
				)
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
			$message = __( 'The response from the Google AI API does not include any candidates with content.', 'ai-services' );

			$errors = array_unique( $errors );
			if ( count( $errors ) > 0 ) {
				$message .= ' ' . sprintf(
					/* translators: %s: finish reason code */
					__( 'Finish reason: %s', 'ai-services' ),
					implode(
						wp_get_list_item_separator(),
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

	/**
	 * Transforms a given Content instance into the format required for the API request.
	 *
	 * @since 0.1.1
	 *
	 * @param Content $content The content instance.
	 * @return array<string, mixed> The content data for the API request.
	 *
	 * @throws InvalidArgumentException Thrown if the content is invalid.
	 */
	private function prepare_content_for_api_request( Content $content ): array {
		$content = $content->to_array();

		// The Google AI API expects inlineData blobs to be without the prefix.
		foreach ( $content['parts'] as $index => $part ) {
			if ( isset( $part['inlineData']['data'] ) ) {
				$content['parts'][ $index ]['inlineData']['data'] = preg_replace(
					'/^data:image\/[a-z]+;base64,/',
					'',
					$part['inlineData']['data']
				);
			}
		}

		return $content;
	}
}
