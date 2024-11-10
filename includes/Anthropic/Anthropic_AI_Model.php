<?php
/**
 * Class Felix_Arntz\AI_Services\Anthropic\Anthropic_AI_Model
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Anthropic;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use Generator;
use InvalidArgumentException;

/**
 * Class representing an Anthropic AI model.
 *
 * @since 0.1.0
 */
class Anthropic_AI_Model implements Generative_AI_Model, With_Multimodal_Input, With_Text_Generation {
	use With_Text_Generation_Trait;

	/**
	 * The Anthropic AI API instance.
	 *
	 * @since 0.1.0
	 * @var Anthropic_AI_API_Client
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
	 * @param Anthropic_AI_API_Client $api             The Anthropic AI API instance.
	 * @param string                  $model           The model slug.
	 * @param array<string, mixed>    $model_params    Optional. Additional model parameters. See
	 *                                                 {@see Anthropic_AI_Service::get_model()} for the list of
	 *                                                 available parameters. Default empty array.
	 * @param array<string, mixed>    $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Anthropic_AI_API_Client $api, string $model, array $model_params = array(), array $request_options = array() ) {
		$this->api             = $api;
		$this->request_options = $request_options;

		$this->model = $model;

		if ( isset( $model_params['generationConfig'] ) ) {
			if ( $model_params['generationConfig'] instanceof Generation_Config ) {
				$this->generation_config = $model_params['generationConfig'];
			} else {
				$this->generation_config = Generation_Config::from_array( $model_params['generationConfig'] );
			}
		}

		if ( isset( $model_params['systemInstruction'] ) ) {
			$this->system_instruction = Formatter::format_system_instruction( $model_params['systemInstruction'] );
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
		$params = $this->prepare_generate_text_params( $contents );

		$request  = $this->api->create_generate_content_request(
			$this->model,
			$params,
			array_merge(
				$this->request_options,
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
	 * Sends a request to generate text content, streaming the response.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Generator<Candidates> Generator that yields the chunks of response candidates with generated text
	 *                               content - usually just one candidate.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_stream_generate_text_request( array $contents, array $request_options ): Generator {
		$params = $this->prepare_generate_text_params( $contents );

		$request  = $this->api->create_stream_generate_content_request(
			$this->model,
			$params,
			array_merge(
				$this->request_options,
				$request_options
			)
		);
		$response = $this->api->make_request( $request );

		return $this->api->process_response_stream(
			$response,
			function ( $response_data, $prev_chunk_candidates ) {
				if (
					null !== $prev_chunk_candidates &&
					isset( $response_data['type'] ) &&
					'ping' === $response_data['type']
				) {
					// Nothing new in this chunk, so no need to return anything.
					return null;
				}

				return $this->get_response_candidates( $response_data, $prev_chunk_candidates );
			}
		);
	}

	/**
	 * Prepares the API request parameters for generating text content.
	 *
	 * @since n.e.x.t
	 *
	 * @param Content[] $contents The contents to generate text for.
	 * @return array<string, mixed> The parameters for generating text content.
	 */
	private function prepare_generate_text_params( array $contents ): array {
		$transformers = self::get_content_transformers();

		$params = array(
			// TODO: Add support for tools and tool config, to support code generation.
			'messages' => array_map(
				static function ( Content $content ) use ( $transformers ) {
					return Transformer::transform_content( $content, $transformers );
				},
				$contents
			),
		);

		if ( $this->system_instruction ) {
			$params['system'] = Helpers::content_to_text( $this->system_instruction );
		}

		if ( $this->generation_config ) {
			$params = Transformer::transform_generation_config_params(
				array_merge( $this->generation_config->get_additional_args(), $params ),
				$this->generation_config,
				self::get_generation_config_transformers()
			);
		}

		return $params;
	}

	/**
	 * Extracts the candidates with content from the response.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $response_data         The response data.
	 * @param ?Candidates          $prev_chunk_candidates The candidates from the previous chunk in case of a streaming
	 *                                                    response, or null.
	 * @return Candidates The candidates with content parts.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not have any candidates with content.
	 */
	private function get_response_candidates( array $response_data, ?Candidates $prev_chunk_candidates = null ): Candidates {
		$candidates = new Candidates();

		if ( null === $prev_chunk_candidates ) {
			if ( ! isset( $response_data['type'] ) || 'message_start' !== $response_data['type'] ) {
				// Regular (non-streaming) response.
				$chunk_data = $response_data;
			} elseif ( isset( $response_data['type'] ) ) {
				// First chunk of a streaming response.
				if ( ! isset( $response_data['message'] ) ) {
					throw $this->api->create_missing_response_key_exception( 'message' );
				}
				$chunk_data = $response_data['message'];
			} else {
				throw $this->api->create_response_exception(
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					__( 'Unexpected response missing previous stream chunk.', 'ai-services' )
				);
			}

			$candidates->add_candidate(
				new Candidate(
					$this->prepare_api_response_for_content( $chunk_data ),
					$chunk_data
				)
			);

			return $candidates;
		}

		// Subsequent chunk of a streaming response.
		$candidate_data = $this->merge_candidate_chunk(
			$prev_chunk_candidates->get( 0 )->to_array(),
			$response_data
		);

		$candidates->add_candidate(
			Candidate::from_array( $candidate_data )
		);

		return $candidates;
	}

	/**
	 * Merges a streaming response chunk with the previous candidate data.
	 *
	 * @since n.e.x.t
	 *
	 * @param array<string, mixed> $candidate_data The candidate data from the previous chunk.
	 * @param array<string, mixed> $chunk_data     The response chunk data.
	 * @return array<string, mixed> The merged candidate data.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function merge_candidate_chunk( array $candidate_data, array $chunk_data ): array {
		if ( ! isset( $chunk_data['type'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->api->create_response_exception( __( 'Unexpected streaming chunk response.', 'ai-services' ) );
		}

		switch ( $chunk_data['type'] ) {
			case 'content_block_start':
				// First chunk of a new content part in a streaming response.
				if ( ! isset( $chunk_data['content_block'] ) ) {
					throw $this->api->create_missing_response_key_exception( 'content_block' );
				}
				$candidate_data['content']['parts'] = array(
					array( 'text' => $chunk_data['content_block']['text'] ),
				);
				break;
			case 'content_block_delta':
				if ( ! isset( $chunk_data['delta']['text'] ) ) {
					throw $this->api->create_missing_response_key_exception( 'delta.text' );
				}
				$candidate_data['content']['parts'][0]['text'] = $chunk_data['delta']['text'];
				break;
			case 'message_delta':
				if ( ! isset( $chunk_data['delta'] ) ) {
					throw $this->api->create_missing_response_key_exception( 'delta' );
				}
				$candidate_data['content']['parts'][0]['text'] = '';
				$candidate_data                                = array_merge(
					$candidate_data,
					$chunk_data['delta'],
					isset( $chunk_data['usage'] ) ? array( 'usage' => $chunk_data['usage'] ) : array()
				);
				break;
			case 'content_block_stop':
			case 'message_stop':
				// If there was a previous content block, ensure it is ends in a double newline.
				if (
					'' !== $candidate_data['content']['parts'][0]['text'] &&
					! str_ends_with( $candidate_data['content']['parts'][0]['text'], "\n\n" )
				) {
					$text_suffix = str_ends_with( $candidate_data['content']['parts'][0]['text'], "\n" ) ? "\n" : "\n\n";
				} else {
					$text_suffix = '';
				}
				$candidate_data['content']['parts'] = array(
					array( 'text' => $text_suffix ),
				);
				break;
			default:
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				throw $this->api->create_response_exception( __( 'Unexpected streaming chunk response.', 'ai-services' ) );
		}

		return $candidate_data;
	}

	/**
	 * Transforms a given API response into a Content instance.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $response_data The API response.
	 * @return Content The Content instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_api_response_for_content( array $response_data ): Content {
		if ( ! isset( $response_data['content'] ) ) {
			throw $this->api->create_missing_response_key_exception( 'content' );
		}

		$role = isset( $response_data['role'] ) && 'user' === $response_data['role']
			? Content_Role::USER
			: Content_Role::MODEL;

		$parts = array();
		foreach ( $response_data['content'] as $part ) {
			// TODO: Support decoding tool call responses.
			if ( 'text' === $part['type'] ) {
				$parts[] = array( 'text' => $part['text'] );
			} else {
				throw $this->api->create_response_exception(
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					__( 'The response includes an unexpected content part.', 'ai-services' )
				);
			}
		}

		return Content::from_array(
			array(
				'role'  => $role,
				'parts' => $parts,
			)
		);
	}

	/**
	 * Gets the content transformers.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, callable> The content transformers.
	 */
	private static function get_content_transformers(): array {
		return array(
			'role'    => static function ( Content $content ) {
				if ( $content->get_role() === Content_Role::MODEL ) {
					return 'assistant';
				}
				return 'user';
			},
			'content' => static function ( Content $content ) {
				$parts = array();
				foreach ( $content->get_parts() as $part ) {
					if ( $part instanceof Text_Part ) {
						$parts[] = array(
							'type' => 'text',
							'text' => $part->get_text(),
						);
					} elseif ( $part instanceof Inline_Data_Part ) {
						$mime_type = $part->get_mime_type();
						if ( ! str_starts_with( $mime_type, 'image/' ) ) {
							throw new InvalidArgumentException(
								esc_html__( 'Invalid content part: The Anthropic API only supports text and inline image parts.', 'ai-services' )
							);
						}
						$parts[] = array(
							'type'   => 'image',
							'source' => array(
								'type'       => 'base64',
								'media_type' => $mime_type,
								'data'       => $part->get_base64_data(),
							),
						);
					} else {
						throw new InvalidArgumentException(
							esc_html__( 'Invalid content part: The Anthropic API only supports text and inline image parts.', 'ai-services' )
						);
					}
				}
				return $parts;
			},
		);
	}

	/**
	 * Gets the generation configuration transformers.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, callable> The generation configuration transformers.
	 */
	private static function get_generation_config_transformers(): array {
		return array(
			'stop_sequences' => static function ( Generation_Config $config ) {
				return $config->get_stop_sequences();
			},
			'max_tokens'     => static function ( Generation_Config $config ) {
				$max_tokens = $config->get_max_output_tokens();
				if ( ! $max_tokens ) {
					// The 'max_tokens' parameter is required in the Anthropic API, so we need a default.
					return 4096;
				}
				return $max_tokens;
			},
			'temperature'    => static function ( Generation_Config $config ) {
				$temperature = $config->get_temperature();
				if ( $temperature > 1.0 ) {
					// The Anthropic API only supports a temperature of up to 1.0, so we need to cap it.
					return 1.0;
				}
				return $temperature;
			},
			'top_p'          => static function ( Generation_Config $config ) {
				return $config->get_top_p();
			},
			'top_k'          => static function ( Generation_Config $config ) {
				return $config->get_top_k();
			},
		);
	}
}
