<?php
/**
 * Class Felix_Arntz\AI_Services\Google\Google_AI_Image_Generation_Model
 *
 * @since 0.5.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Google;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_Image_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\With_API_Client_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Image_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use InvalidArgumentException;

/**
 * Class representing a Google image generation AI model.
 *
 * @since 0.5.0
 */
class Google_AI_Image_Generation_Model extends Abstract_AI_Model implements With_API_Client, With_Image_Generation {
	use With_API_Client_Trait;
	use With_Image_Generation_Trait;

	/**
	 * The generation configuration.
	 *
	 * @since 0.5.0
	 * @var Image_Generation_Config|null
	 */
	protected $generation_config;

	/**
	 * The system instruction.
	 *
	 * @since 0.5.0
	 * @var Content|null
	 */
	protected $system_instruction;

	/**
	 * Constructor.
	 *
	 * @since 0.5.0
	 *
	 * @param Generative_AI_API_Client $api_client      The AI API client instance.
	 * @param string                   $model           The model slug.
	 * @param array<string, mixed>     $model_params    Optional. Additional model parameters. See
	 *                                                  {@see Google_AI_Service::get_model()} for the list of available
	 *                                                  parameters. Default empty array.
	 * @param array<string, mixed>     $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Generative_AI_API_Client $api_client, string $model, array $model_params = array(), array $request_options = array() ) {
		$this->set_api_client( $api_client );

		// Since image generation can be heavy, increase default request timeout to 30 seconds.
		if ( ! isset( $request_options['timeout'] ) ) {
			$request_options['timeout'] = 30;
		}

		parent::__construct( $model, $model_params, $request_options );
	}

	/**
	 * Sets the model parameters on the class instance.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	protected function set_model_params( array $model_params ): void {
		$this->set_props_from_args(
			array(
				array(
					'arg_key'           => 'generationConfig',
					'property_name'     => 'generation_config',
					'sanitize_callback' => static function ( $generation_config ) {
						if ( $generation_config instanceof Image_Generation_Config ) {
							return $generation_config;
						}
						return Image_Generation_Config::from_array( $generation_config );
					},
				),
			),
			$model_params
		);

		if ( isset( $model_params['systemInstruction'] ) ) {
			$this->system_instruction = Formatter::format_system_instruction( $model_params['systemInstruction'] );
		}
	}

	/**
	 * Sends a request to generate an image.
	 *
	 * @since 0.5.0
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_generate_image_request( array $contents, array $request_options ): Candidates {
		$api    = $this->get_api_client();
		$params = $this->prepare_generate_image_params( $contents );

		$model = $this->get_model_slug();
		if ( ! str_contains( $model, '/' ) ) {
			$model = 'models/' . $model;
		}

		$request  = $api->create_post_request(
			"{$model}:predict",
			$params,
			array_merge(
				$this->get_request_options(),
				$request_options
			)
		);
		$response = $api->make_request( $request );

		return $api->process_response_data(
			$response,
			function ( $response_data ) {
				return $this->get_response_candidates( $response_data );
			}
		);
	}

	/**
	 * Prepares the API request parameters for generating an image.
	 *
	 * @since 0.5.0
	 *
	 * @param Content[] $contents The contents to generate text for.
	 * @return array<string, mixed> The parameters for generating an image.
	 */
	private function prepare_generate_image_params( array $contents ): array {
		if ( count( $contents ) > 1 ) {
			$contents = array( $contents[ count( $contents ) - 1 ] );
		}
		if ( $this->system_instruction ) {
			$contents = array_merge( array( $this->system_instruction ), $contents );
		}

		$params = array(
			'instances' => array(
				array(
					'prompt' => trim(
						implode(
							'\n\n',
							array_map(
								array( Helpers::class, 'content_to_text' ),
								$contents
							)
						)
					),
				),
			),
		);

		if ( $this->generation_config ) {
			$params               = array_merge( $this->generation_config->get_additional_args(), $params );
			$params['parameters'] = Transformer::transform_generation_config_params(
				isset( $params['generationConfig'] ) && is_array( $params['generationConfig'] ) ? $params['generationConfig'] : array(),
				$this->generation_config,
				self::get_generation_config_transformers()
			);
		} else {
			// Override some API defaults.
			$params['parameters'] = array( 'sampleCount' => 1 );
		}

		return $params;
	}

	/**
	 * Extracts the candidates with content from the response.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $response_data The response data.
	 * @return Candidates The candidates with content parts.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not have any candidates with content.
	 */
	private function get_response_candidates( array $response_data ): Candidates {
		if ( ! isset( $response_data['predictions'] ) ) {
			throw $this->get_api_client()->create_missing_response_key_exception( 'predictions' );
		}

		$other_data = $response_data;
		unset( $other_data['predictions'] );

		$candidates = new Candidates();
		foreach ( $response_data['predictions'] as $index => $candidate_data ) {
			$other_candidate_data = $candidate_data;
			unset( $other_candidate_data['bytesBase64Encoded'], $other_candidate_data['url'], $other_candidate_data['mimeType'] );

			$candidates->add_candidate(
				new Candidate(
					$this->prepare_candidate_content( $candidate_data, $index ),
					array_merge( $other_candidate_data, $other_data )
				)
			);
		}

		return $candidates;
	}

	/**
	 * Transforms a given candidate from the API response into a Content instance.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $candidate_data The API response candidate data.
	 * @param int                  $index       The index of the candidate in the response.
	 * @return Content The Content instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_candidate_content( array $candidate_data, int $index ): Content {
		if ( ! isset( $candidate_data['bytesBase64Encoded'] ) && ! isset( $candidate_data['url'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->get_api_client()->create_missing_response_key_exception( "predictions.{$index}.bytesBase64Encoded" );
		}

		$mime_type = isset( $candidate_data['mimeType'] ) ? $candidate_data['mimeType'] : 'image/png';

		if ( isset( $candidate_data['bytesBase64Encoded'] ) ) {
			$part = array(
				'inlineData' => array(
					'mimeType' => $mime_type,
					'data'     => Helpers::base64_data_to_base64_data_url( $candidate_data['bytesBase64Encoded'], $mime_type ),
				),
			);
		} else {
			$part = array(
				'fileData' => array(
					'mimeType' => $mime_type,
					'fileUri'  => $candidate_data['url'],
				),
			);
		}

		return new Content(
			Content_Role::MODEL,
			Parts::from_array( array( $part ) )
		);
	}

	/**
	 * Gets the generation configuration transformers.
	 *
	 * @since 0.5.0
	 *
	 * @return array<string, callable> The generation configuration transformers.
	 */
	private static function get_generation_config_transformers(): array {
		return array(
			'outputOptions' => static function ( Image_Generation_Config $config ) {
				$output_mime = $config->get_response_mime_type();
				if ( ! $output_mime ) {
					return null;
				}
				return array( 'mimeType' => $output_mime );
			},
			'sampleCount'   => static function ( Image_Generation_Config $config ) {
				return $config->get_candidate_count();
			},
			'aspectRatio'   => static function ( Image_Generation_Config $config ) {
				return $config->get_aspect_ratio();
			},
			'responseType'  => static function ( Image_Generation_Config $config ) {
				// The API does not allow setting the response type and always uses base64-encoded data.
				$response_type = $config->get_response_type();
				if ( 'file_data' === $response_type ) {
					throw new InvalidArgumentException(
						esc_html__( 'Only base64-encoded data is supported as the response type.', 'ai-services' )
					);
				}
				return '';
			},
		);
	}
}
