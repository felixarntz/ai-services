<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Text_To_Speech_Model
 *
 * @since n.e.x.t
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Exception;
use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Text_To_Speech_Config;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_To_Speech;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_System_Instruction_Trait;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_Text_To_Speech_Config_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_API_Client_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_To_Speech_Trait;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use InvalidArgumentException;

/**
 * Class representing an OpenAI text to speech AI model.
 *
 * @since n.e.x.t
 */
class OpenAI_AI_Text_To_Speech_Model extends Abstract_AI_Model implements With_API_Client, With_Text_To_Speech {
	use With_API_Client_Trait;
	use With_Text_To_Speech_Trait;
	use Model_Param_Text_To_Speech_Config_Trait;
	use Model_Param_System_Instruction_Trait;

	/**
	 * The expected MIME type of the generated audio.
	 *
	 * Internal temporary storage to not have to pass it around, as it should not be part of the interface.
	 *
	 * @since n.e.x.t
	 * @var string
	 */
	private $expected_mime_type = 'audio/mpeg';

	/**
	 * Constructor.
	 *
	 * @since n.e.x.t
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

		$this->set_text_to_speech_config_from_model_params( $model_params );
		$this->set_system_instruction_from_model_params( $model_params );

		// Since text to speech can be heavy, increase default request timeout to 30 seconds.
		if ( ! isset( $request_options['timeout'] ) ) {
			$request_options['timeout'] = 30;
		}
		$this->set_request_options( $request_options );
	}

	/**
	 * Sends a request to transform text to speech.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        The content to transform to speech.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Candidates The response candidates with generated speech - usually just one.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_text_to_speech_request( array $contents, array $request_options ): Candidates {
		$api    = $this->get_api_client();
		$params = $this->prepare_text_to_speech_params( $contents );

		$params['model'] = $this->get_model_slug();

		$request  = $api->create_post_request(
			'audio/speech',
			$params,
			array_merge(
				$this->get_request_options(),
				$request_options
			)
		);
		$response = $api->make_request( $request );

		// Set the expected MIME type based on the request parameters.
		$expected_mime_type = isset( $params['response_format'] ) && 'mp3' !== $params['response_format'] ? 'audio/' . $params['response_format'] : 'audio/mpeg';

		return $api->process_response_body(
			$response,
			function ( $response_body ) use ( $expected_mime_type ) {
				// Set the expected MIME type for processing the response.
				$this->expected_mime_type = $expected_mime_type;
				try {
					$result = $this->get_response_candidates( $response_body );
				} catch ( Exception $e ) {
					// Reset the expected MIME type.
					$this->expected_mime_type = 'audio/mpeg';
					throw $e;
				}
				// Reset the expected MIME type.
				$this->expected_mime_type = 'audio/mpeg';
				return $result;
			}
		);
	}

	/**
	 * Prepares the API request parameters for transforming text to speech.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $contents The contents to transform to speech.
	 * @return array<string, mixed> The parameters for transforming text to speech.
	 *
	 * @throws InvalidArgumentException Thrown if configuration values are not supported by the model.
	 */
	protected function prepare_text_to_speech_params( array $contents ): array {
		/*
		 * The OpenAI API specification only allows a single prompt, as a text string.
		 * For this reason, we only use the last content in case multiple messages are provided.
		 */
		$last_content = end( $contents );

		$params = Transformer::transform_content(
			$last_content,
			$this->get_content_transformers()
		);

		$text_to_speech_config = $this->get_text_to_speech_config();
		if ( $text_to_speech_config ) {
			$params = Transformer::transform_generation_config_params(
				array_merge( $text_to_speech_config->get_additional_args(), $params ),
				$text_to_speech_config,
				$this->get_generation_config_transformers()
			);
		} else {
			// Providing a 'voice' is required.
			$params['voice'] = 'alloy';
		}

		if ( $this->get_system_instruction() ) {
			$params['instructions'] = Helpers::content_to_text( $this->get_system_instruction() );
		}

		return $params;
	}

	/**
	 * Extracts the candidates with content from the response.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $response_body The response body, as raw audio bytes.
	 * @return Candidates The candidates with content parts.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not have any candidates with content.
	 */
	private function get_response_candidates( string $response_body ): Candidates {
		$candidates = new Candidates();
		$candidates->add_candidate(
			new Candidate(
				$this->prepare_response_candidate_content( $response_body ),
				array() // No other data for raw audio response.
			)
		);

		return $candidates;
	}

	/**
	 * Transforms a given candidate from the API response into a Content instance.
	 *
	 * @since n.e.x.t
	 *
	 * @param string $response_body The response body, as raw audio bytes.
	 * @return Content The Content instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_response_candidate_content( string $response_body ): Content {
		$base64_audio = base64_encode( $response_body ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		if ( '' === $base64_audio ) {
			throw $this->get_api_client()->create_response_exception(
				'Failed to base64 encode the audio response.'
			);
		}

		/*
		 * This is set temporarily in a class property based on the request parameters, since the response does not
		 * include the MIME type.
		 */
		$expected_mime_type = $this->expected_mime_type;

		$part = array(
			'inlineData' => array(
				'mimeType' => $expected_mime_type,
				'data'     => Helpers::base64_data_to_base64_data_url( $base64_audio, $expected_mime_type ),
			),
		);

		return new Content(
			Content_Role::MODEL,
			Parts::from_array( array( $part ) )
		);
	}

	/**
	 * Gets the content transformers.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, callable> The content transformers.
	 */
	private function get_content_transformers(): array {
		return array(
			'input' => static function ( Content $content ) {
				return Helpers::content_to_text( $content );
			},
		);
	}

	/**
	 * Gets the generation configuration transformers.
	 *
	 * @since n.e.x.t
	 *
	 * @return array<string, callable> The generation configuration transformers.
	 */
	private function get_generation_config_transformers(): array {
		return array(
			'voice'           => static function ( Text_To_Speech_Config $config ) {
				$voice = $config->get_voice();
				if ( ! $voice ) {
					$voice = 'alloy'; // Default voice if not set.
				}
				return $voice;
			},
			'response_format' => static function ( Text_To_Speech_Config $config ) {
				$mime_type = $config->get_response_mime_type();
				if ( ! $mime_type ) {
					return '';
				}
				if ( 'audio/mpeg' === $mime_type ) {
					return 'mp3';
				}
				return preg_replace( '/^audio\//', '', $mime_type );
			},
		);
	}
}
