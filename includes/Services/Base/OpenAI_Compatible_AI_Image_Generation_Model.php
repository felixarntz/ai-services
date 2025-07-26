<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Base\OpenAI_Compatible_AI_Image_Generation_Model
 *
 * @since 0.7.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Base;

use Exception;
use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_Image_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_Image_Generation_Config_Trait;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_System_Instruction_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_API_Client_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Image_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use InvalidArgumentException;

/**
 * Generic implementation of an OpenAI API compatible text generation AI model.
 *
 * @since 0.7.0
 */
class OpenAI_Compatible_AI_Image_Generation_Model extends Abstract_AI_Model implements With_API_Client, With_Image_Generation {
	use With_API_Client_Trait;
	use With_Image_Generation_Trait;
	use Model_Param_Image_Generation_Config_Trait;
	use Model_Param_System_Instruction_Trait;

	/**
	 * The expected MIME type of the generated image.
	 *
	 * Internal temporary storage to not have to pass it around, as it should not be part of the interface.
	 *
	 * @since 0.7.0
	 * @var string
	 */
	private $expected_mime_type = 'image/png';

	/**
	 * Constructor.
	 *
	 * @since 0.7.0
	 *
	 * @param Generative_AI_API_Client $api_client      The AI API client instance.
	 * @param Model_Metadata           $metadata        The model metadata.
	 * @param array<string, mixed>     $model_params    Optional. Additional model parameters. See
	 *                                                  {@see OpenAI_AI_Service::get_model()} for the list of available
	 *                                                  parameters. Default empty array.
	 * @param array<string, mixed>     $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Generative_AI_API_Client $api_client, Model_Metadata $metadata, array $model_params = array(), array $request_options = array() ) {
		$this->set_api_client( $api_client );
		$this->set_model_metadata( $metadata );

		$this->set_image_generation_config_from_model_params( $model_params );
		$this->set_system_instruction_from_model_params( $model_params );

		// Since image generation can be heavy, increase default request timeout to 30 seconds.
		if ( ! isset( $request_options['timeout'] ) ) {
			$request_options['timeout'] = 30;
		}
		$this->set_request_options( $request_options );
	}

	/**
	 * Sends a request to generate an image.
	 *
	 * @since 0.7.0
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated text content - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	final protected function send_generate_image_request( array $contents, array $request_options ): Candidates {
		$api    = $this->get_api_client();
		$route  = $this->get_generate_image_route( $contents );
		$params = $this->prepare_generate_image_params( $contents );

		$params['model'] = $this->get_model_slug();

		$request  = $api->create_post_request(
			$route,
			$params,
			array_merge(
				$this->get_request_options(),
				$request_options
			)
		);
		$response = $api->make_request( $request );

		$expected_mime_type = isset( $params['output_format'] ) ? "image/{$params['output_format']}" : 'image/png';

		return $api->process_response_data(
			$response,
			function ( $response_data ) use ( $expected_mime_type ) {
				// Set the expected MIME type for processing the response.
				$this->expected_mime_type = $expected_mime_type;
				try {
					$result = $this->get_response_candidates( $response_data );
				} catch ( Exception $e ) {
					// Reset the expected MIME type.
					$this->expected_mime_type = 'image/png';
					throw $e;
				}
				// Reset the expected MIME type.
				$this->expected_mime_type = 'image/png';
				return $result;
			}
		);
	}

	/**
	 * Gets the API route for generating an image.
	 *
	 * @since 0.7.0
	 *
	 * @param Content[] $contents The contents to generate an image for.
	 * @return string The route for generating an image.
	 */
	protected function get_generate_image_route( array $contents ): string {
		return 'images/generations';
	}

	/**
	 * Prepares the API request parameters for generating an image.
	 *
	 * @since 0.7.0
	 *
	 * @param Content[] $contents The contents to generate an image for.
	 * @return array<string, mixed> The parameters for generating an image.
	 *
	 * @throws InvalidArgumentException Thrown if configuration values are not supported by the model.
	 */
	protected function prepare_generate_image_params( array $contents ): array {
		/*
		 * The OpenAI API specification only allows a single prompt, as a text string.
		 * For this reason, we only use the last content in case multiple messages are provided, and we prepend the
		 * system instruction if set.
		 */
		$last_content = end( $contents );

		$params = Transformer::transform_content(
			$last_content,
			$this->get_content_transformers()
		);

		if ( $this->get_system_instruction() ) {
			$params['prompt'] = Helpers::content_to_text( $this->get_system_instruction() ) . "\n\n" . $params['prompt'];
		}

		$generation_config = $this->get_image_generation_config();
		if ( $generation_config ) {
			$params = Transformer::transform_generation_config_params(
				array_merge( $generation_config->get_additional_args(), $params ),
				$generation_config,
				$this->get_generation_config_transformers()
			);
		} else {
			// Override some API defaults.
			$params['n']               = 1;
			$params['response_format'] = 'b64_json';
		}

		return $params;
	}

	/**
	 * Extracts the candidates with content from the response.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $response_data The response data.
	 * @return Candidates The candidates with content parts.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not have any candidates with content.
	 */
	private function get_response_candidates( array $response_data ): Candidates {
		if ( ! isset( $response_data['data'] ) ) {
			throw $this->get_api_client()->create_missing_response_key_exception( 'data' );
		}

		$other_data = $response_data;
		unset( $other_data['data'] );

		$candidates = new Candidates();
		foreach ( $response_data['data'] as $index => $candidate_data ) {
			$other_candidate_data = $candidate_data;
			unset( $other_candidate_data['b64_json'], $other_candidate_data['url'], $other_candidate_data['mimeType'] );

			$candidates->add_candidate(
				new Candidate(
					$this->prepare_response_candidate_content( $candidate_data, $index ),
					array_merge( $other_candidate_data, $other_data )
				)
			);
		}

		return $candidates;
	}

	/**
	 * Transforms a given candidate from the API response into a Content instance.
	 *
	 * @since 0.7.0
	 *
	 * @param array<string, mixed> $candidate_data The API response candidate data.
	 * @param int                  $index          The index of the candidate in the response.
	 * @return Content The Content instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_response_candidate_content( array $candidate_data, int $index ): Content {
		if ( ! isset( $candidate_data['b64_json'] ) && ! isset( $candidate_data['url'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->get_api_client()->create_missing_response_key_exception( "data.{$index}.b64_json" );
		}

		/*
		 * This is set temporarily in a class property based on the request parameters, since the response does not
		 * include the MIME type.
		 */
		$expected_mime_type = $this->expected_mime_type;

		if ( isset( $candidate_data['b64_json'] ) ) {
			$part = array(
				'inlineData' => array(
					'mimeType' => $expected_mime_type,
					'data'     => Helpers::base64_data_to_base64_data_url( $candidate_data['b64_json'], $expected_mime_type ),
				),
			);
		} else {
			$part = array(
				'fileData' => array(
					'mimeType' => $expected_mime_type,
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
	 * Gets the content transformers.
	 *
	 * @since 0.7.0
	 *
	 * @return array<string, callable> The content transformers.
	 */
	protected function get_content_transformers(): array {
		return array(
			'prompt' => static function ( Content $content ) {
				return Helpers::content_to_text( $content );
			},
		);
	}

	/**
	 * Gets the generation configuration transformers.
	 *
	 * @since 0.7.0
	 *
	 * @return array<string, callable> The generation configuration transformers.
	 */
	protected function get_generation_config_transformers(): array {
		return array(
			'n'               => static function ( Image_Generation_Config $config ) {
				return $config->get_candidate_count();
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
