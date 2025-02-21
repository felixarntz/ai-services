<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Image_Generation_Model
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_Image_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\With_Image_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use InvalidArgumentException;

/**
 * Class representing an OpenAI image generation AI model.
 *
 * @since n.e.x.t
 */
class OpenAI_AI_Image_Generation_Model extends Abstract_AI_Model implements With_Image_Generation {
	use With_Image_Generation_Trait;

	/**
	 * The OpenAI AI API instance.
	 *
	 * @since n.e.x.t
	 * @var OpenAI_AI_API_Client
	 */
	protected $api;

	/**
	 * The generation configuration.
	 *
	 * @since n.e.x.t
	 * @var Image_Generation_Config|null
	 */
	protected $generation_config;

	/**
	 * The system instruction.
	 *
	 * @since n.e.x.t
	 * @var Content|null
	 */
	protected $system_instruction;

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
	 *
	 * @param OpenAI_AI_API_Client $api             The OpenAI AI API instance.
	 * @param string               $model           The model slug.
	 * @param array<string, mixed> $model_params    Optional. Additional model parameters. See
	 *                                              {@see OpenAI_AI_Service::get_model()} for the list of available
	 *                                              parameters. Default empty array.
	 * @param array<string, mixed> $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( OpenAI_AI_API_Client $api, string $model, array $model_params = array(), array $request_options = array() ) {
		$this->api = $api;

		parent::__construct( $model, $model_params, $request_options );
	}

	/**
	 * Sets the model parameters on the class instance.
	 *
	 * @since n.e.x.t
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
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_generate_image_request( array $contents, array $request_options ): Candidates {
		$params = $this->prepare_generate_image_params( $contents );

		$request  = $this->api->create_generate_images_request(
			$this->get_model_slug(),
			$params,
			array_merge(
				$this->get_request_options(),
				$request_options
			)
		);
		$response = $this->api->make_request( $request );

		return $this->api->process_response_data(
			$response,
			function ( $response_data ) {
				return $this->get_response_candidates( $response_data );
			}
		);
	}

	/**
	 * Prepares the API request parameters for generating an image.
	 *
	 * @since n.e.x.t
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
			'prompt' => trim(
				implode(
					'\n\n',
					array_map(
						array( Helpers::class, 'content_to_text' ),
						$contents
					)
				)
			),
		);

		if ( $this->generation_config ) {
			$params = Transformer::transform_generation_config_params(
				array_merge( $this->generation_config->get_additional_args(), $params ),
				$this->generation_config,
				self::get_generation_config_transformers()
			);
		} else {
			// Override the API's default response format to return base64-encoded JSON data.
			$params['response_format'] = 'b64_json';
		}

		return $params;
	}

	/**
	 * Extracts the candidates with content from the response.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $response_data The response data.
	 * @return Candidates The candidates with content parts.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not have any candidates with content.
	 */
	private function get_response_candidates( array $response_data ): Candidates {
		if ( ! isset( $response_data['data'] ) ) {
			throw $this->api->create_missing_response_key_exception( 'data' );
		}

		$other_data = $response_data;
		unset( $other_data['data'] );

		$candidates = new Candidates();
		foreach ( $response_data['data'] as $index => $candidate_data ) {
			$other_candidate_data = $candidate_data;
			unset( $other_candidate_data['b64_json'], $other_candidate_data['url'], $other_candidate_data['mimeType'] );

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
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $candidate_data The API response candidate data.
	 * @param int                  $index       The index of the candidate in the response.
	 * @return Content The Content instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_candidate_content( array $candidate_data, int $index ): Content {
		if ( ! isset( $candidate_data['b64_json'] ) && ! isset( $candidate_data['url'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->api->create_missing_response_key_exception( "data.{$index}.b64_json" );
		}

		// The MIME type 'image/png' is hardcoded here because the OpenAI API only supports PNG images.
		$mime_type = 'image/png';

		if ( isset( $candidate_data['b64_json'] ) ) {
			if ( ! str_starts_with( $candidate_data['b64_json'], 'data:' ) ) {
				$candidate_data['b64_json'] = 'data:' . $mime_type . ';base64,' . $candidate_data['b64_json'];
			}
			$part = array(
				'inlineData' => array(
					'mimeType' => $mime_type,
					'data'     => $candidate_data['b64_json'],
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
	 * @since n.e.x.t
	 *
	 * @return array<string, callable> The generation configuration transformers.
	 */
	private static function get_generation_config_transformers(): array {
		return array(
			'n'               => static function ( Image_Generation_Config $config ) {
				return $config->get_candidate_count();
			},
			'size'            => static function ( Image_Generation_Config $config ) {
				$ratio = $config->get_aspect_ratio();
				switch ( $ratio ) {
					case '1:1':
						return '1024x1024';
					case '16:9':
						return '1024x576';
					case '9:16':
						return '576x1024';
					case '4:3':
						return '1024x768';
					case '3:4':
						return '768x1024';
				}
				return '';
			},
			'response_format' => static function ( Image_Generation_Config $config ) {
				// The default in the API is to return URLs, but we want to return base64-encoded data by default.
				$response_type = $config->get_response_type();
				if ( ! $response_type ) {
					return 'b64_json';
				}
				return 'file_data' === $response_type ? 'url' : 'b64_json';
			},
		);
	}
}
