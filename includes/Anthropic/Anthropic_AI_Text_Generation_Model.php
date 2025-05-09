<?php
/**
 * Class Felix_Arntz\AI_Services\Anthropic\Anthropic_AI_Text_Generation_Model
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
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Call_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Response_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tool_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;
use Felix_Arntz\AI_Services\Services\API\Types\Tools\Function_Declarations_Tool;
use Felix_Arntz\AI_Services\Services\Base\Abstract_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_API_Client;
use Felix_Arntz\AI_Services\Services\Contracts\With_Chat_History;
use Felix_Arntz\AI_Services\Services\Contracts\With_Function_Calling;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\With_API_Client_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Chat_History_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use Generator;
use InvalidArgumentException;

/**
 * Class representing an Anthropic text generation AI model.
 *
 * @since 0.1.0
 * @since 0.5.0 Renamed from `Anthropic_AI_Model`.
 */
class Anthropic_AI_Text_Generation_Model extends Abstract_AI_Model implements With_API_Client, With_Text_Generation, With_Chat_History, With_Function_Calling, With_Multimodal_Input {
	use With_API_Client_Trait;
	use With_Text_Generation_Trait;
	use With_Chat_History_Trait;

	/**
	 * The tools available to use for the model.
	 *
	 * @since 0.5.0
	 * @var Tools|null
	 */
	protected $tools;

	/**
	 * The tool configuration, if applicable.
	 *
	 * @since 0.5.0
	 * @var Tool_Config|null
	 */
	protected $tool_config;

	/**
	 * The generation configuration.
	 *
	 * @since 0.1.0
	 * @var Text_Generation_Config|null
	 */
	protected $generation_config;

	/**
	 * The system instruction.
	 *
	 * @since 0.1.0
	 * @var Content|null
	 */
	protected $system_instruction;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Generative_AI_API_Client $api_client      The AI API client instance.
	 * @param string                   $model           The model slug.
	 * @param array<string, mixed>     $model_params    Optional. Additional model parameters. See
	 *                                                  {@see Anthropic_AI_Service::get_model()} for the list of
	 *                                                  available parameters. Default empty array.
	 * @param array<string, mixed>     $request_options Optional. The request options. Default empty array.
	 *
	 * @throws InvalidArgumentException Thrown if the model parameters are invalid.
	 */
	public function __construct( Generative_AI_API_Client $api_client, string $model, array $model_params = array(), array $request_options = array() ) {
		$this->set_api_client( $api_client );

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
					'arg_key'           => 'tools',
					'property_name'     => 'tools',
					'sanitize_callback' => static function ( $tools ) {
						if ( $tools instanceof Tools ) {
							return $tools;
						}
						return Tools::from_array( $tools );
					},
				),
				array(
					'arg_key'           => 'toolConfig',
					'property_name'     => 'tool_config',
					'sanitize_callback' => static function ( $tool_config ) {
						if ( $tool_config instanceof Tool_Config ) {
							return $tool_config;
						}
						return Tool_Config::from_array( $tool_config );
					},
				),
				array(
					'arg_key'           => 'generationConfig',
					'property_name'     => 'generation_config',
					'sanitize_callback' => static function ( $generation_config ) {
						if ( $generation_config instanceof Text_Generation_Config ) {
							return $generation_config;
						}
						return Text_Generation_Config::from_array( $generation_config );
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
		$api    = $this->get_api_client();
		$params = $this->prepare_generate_text_params( $contents );

		$params['model'] = $this->get_model_slug();

		$request  = $api->create_post_request(
			'messages',
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
	 * Sends a request to generate text content, streaming the response.
	 *
	 * @since 0.3.0
	 *
	 * @param Content[]            $contents        Prompts for the content to generate.
	 * @param array<string, mixed> $request_options The request options.
	 * @return Generator<Candidates> Generator that yields the chunks of response candidates with generated text
	 *                               content - usually just one candidate.
	 *
	 * @throws Generative_AI_Exception Thrown if the request fails or the response is invalid.
	 */
	protected function send_stream_generate_text_request( array $contents, array $request_options ): Generator {
		$api    = $this->get_api_client();
		$params = $this->prepare_generate_text_params( $contents );

		$params['model']  = $this->get_model_slug();
		$params['stream'] = true;

		$request  = $api->create_post_request(
			'messages',
			$params,
			array_merge(
				$this->get_request_options(),
				$request_options,
				array( 'stream' => true )
			)
		);
		$response = $api->make_request( $request );

		return $api->process_response_stream(
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
	 * @since 0.3.0
	 *
	 * @param Content[] $contents The contents to generate text for.
	 * @return array<string, mixed> The parameters for generating text content.
	 */
	private function prepare_generate_text_params( array $contents ): array {
		$transformers = self::get_content_transformers();

		$params = array(
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

		if ( $this->tools ) {
			$params['tools'] = $this->prepare_tools_param( $this->tools );
		}

		if ( $this->tool_config ) {
			$params['tool_choice'] = $this->prepare_tool_choice_param( $this->tool_config );
		}

		if ( $this->generation_config ) {
			$params = Transformer::transform_generation_config_params(
				array_merge( $this->generation_config->get_additional_args(), $params ),
				$this->generation_config,
				self::get_generation_config_transformers()
			);
		} else {
			// The 'max_tokens' parameter is required in the Anthropic API, so we need a default.
			$params['max_tokens'] = 4096;
		}

		return $params;
	}

	/**
	 * Extracts the candidates with content from the response.
	 *
	 * @since 0.3.0
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
					throw $this->get_api_client()->create_missing_response_key_exception( 'message' );
				}
				$chunk_data = $response_data['message'];
			} else {
				throw $this->get_api_client()->create_response_exception(
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					__( 'Unexpected response missing previous stream chunk.', 'ai-services' )
				);
			}

			$other_chunk_data = $chunk_data;
			unset( $other_chunk_data['type'], $other_chunk_data['message'] );

			$candidates->add_candidate(
				new Candidate(
					$this->prepare_api_response_for_content( $chunk_data ),
					$other_chunk_data
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
	 * @since 0.3.0
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
			throw $this->get_api_client()->create_response_exception( __( 'Unexpected streaming chunk response.', 'ai-services' ) );
		}

		switch ( $chunk_data['type'] ) {
			case 'content_block_start':
				// First chunk of a new content part in a streaming response.
				if ( ! isset( $chunk_data['content_block'] ) ) {
					throw $this->get_api_client()->create_missing_response_key_exception( 'content_block' );
				}
				$candidate_data['content']['parts'] = array(
					array( 'text' => $chunk_data['content_block']['text'] ),
				);
				break;
			case 'content_block_delta':
				if ( ! isset( $chunk_data['delta']['text'] ) ) {
					throw $this->get_api_client()->create_missing_response_key_exception( 'delta.text' );
				}
				$candidate_data['content']['parts'][0]['text'] = $chunk_data['delta']['text'];
				break;
			case 'message_delta':
				if ( ! isset( $chunk_data['delta'] ) ) {
					throw $this->get_api_client()->create_missing_response_key_exception( 'delta' );
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
				throw $this->get_api_client()->create_response_exception( __( 'Unexpected streaming chunk response.', 'ai-services' ) );
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
			throw $this->get_api_client()->create_missing_response_key_exception( 'content' );
		}

		$role = isset( $response_data['role'] ) && 'user' === $response_data['role']
			? Content_Role::USER
			: Content_Role::MODEL;

		return new Content(
			$role,
			$this->prepare_api_response_content_parts( $response_data )
		);
	}

	/**
	 * Transforms a given API response into a Parts instance.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $response_data The API response.
	 * @return Parts The Parts instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_api_response_content_parts( array $response_data ): Parts {
		$parts = array();
		foreach ( $response_data['content'] as $part ) {
			if ( 'text' === $part['type'] ) {
				$parts[] = array( 'text' => $part['text'] );
			} elseif ( 'tool_use' === $part['type'] ) {
				$parts[] = array(
					'functionCall' => array(
						'id'   => $part['id'],
						'name' => $part['name'],
						'args' => $part['input'],
					),
				);
			} else {
				throw $this->get_api_client()->create_response_exception(
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					__( 'The response includes an unexpected content part.', 'ai-services' )
				);
			}
		}

		return Parts::from_array( $parts );
	}

	/**
	 * Gets the content transformers.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, callable> The content transformers.
	 *
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
								esc_html__( 'The Anthropic API only supports text, inline image, function call, and function response parts.', 'ai-services' )
							);
						}
						$parts[] = array(
							'type'   => 'image',
							'source' => array(
								'type'       => 'base64',
								'media_type' => $mime_type,
								// The Anthropic AI API expects inlineData blobs to be without the prefix.
								'data'       => Helpers::base64_data_url_to_base64_data( $part->get_base64_data() ),
							),
						);
					} elseif ( $part instanceof Function_Call_Part ) {
						$parts[] = array(
							'type'  => 'tool_use',
							'id'    => $part->get_id(),
							'name'  => $part->get_name(),
							'input' => $part->get_args(),
						);
					} elseif ( $part instanceof Function_Response_Part ) {
						$response = $part->get_response();
						$parts[]  = array(
							'type'        => 'tool_result',
							'tool_use_id' => $part->get_id(),
							'content'     => wp_json_encode( $response ),
						);
					} else {
						throw new InvalidArgumentException(
							esc_html__( 'The Anthropic API only supports text, inline image, function call, and function response parts.', 'ai-services' )
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
			'stop_sequences' => static function ( Text_Generation_Config $config ) {
				return $config->get_stop_sequences();
			},
			'max_tokens'     => static function ( Text_Generation_Config $config ) {
				$max_tokens = $config->get_max_output_tokens();
				if ( ! $max_tokens ) {
					// The 'max_tokens' parameter is required in the Anthropic API, so we need a default.
					return 4096;
				}
				return $max_tokens;
			},
			'temperature'    => static function ( Text_Generation_Config $config ) {
				$temperature = $config->get_temperature();
				if ( $temperature > 1.0 ) {
					// The Anthropic API only supports a temperature of up to 1.0, so we need to cap it.
					return 1.0;
				}
				return $temperature;
			},
			'top_p'          => static function ( Text_Generation_Config $config ) {
				return $config->get_top_p();
			},
			'top_k'          => static function ( Text_Generation_Config $config ) {
				return $config->get_top_k();
			},
		);
	}

	/**
	 * Prepares the API request tools parameter for the model.
	 *
	 * @since 0.5.0
	 *
	 * @param Tools $tools The tools to prepare the parameter with.
	 * @return array<string, mixed>[] The tools parameter value.
	 *
	 * @throws InvalidArgumentException Thrown if an invalid tool is provided.
	 */
	private function prepare_tools_param( Tools $tools ): array {
		$tools_param = array();

		foreach ( $tools as $tool ) {
			if ( ! $tool instanceof Function_Declarations_Tool ) {
				throw new InvalidArgumentException(
					esc_html__( 'Invalid tool: Only function declarations tools are supported.', 'ai-services' )
				);
			}

			$function_declarations = $tool->get_function_declarations();
			foreach ( $function_declarations as $declaration ) {
				$tools_param[] = array_filter(
					array(
						'name'         => $declaration['name'],
						'description'  => $declaration['description'] ?? null,
						'input_schema' => $declaration['parameters'] ?? null,
					)
				);
			}
		}

		return $tools_param;
	}

	/**
	 * Prepares the API request tool choice parameter for the model.
	 *
	 * @since 0.5.0
	 *
	 * @param Tool_Config $tool_config The tool config to prepare the parameter with.
	 * @return array<string, mixed> The tool config parameter value.
	 */
	private function prepare_tool_choice_param( Tool_Config $tool_config ): array {
		$tool_choice_param = array(
			// Either 'auto' or 'any'.
			'type' => $tool_config->get_function_call_mode(),
		);

		if ( 'any' === $tool_choice_param['type'] ) {
			$allowed_function_names = $tool_config->get_allowed_function_names();
			if ( count( $allowed_function_names ) === 1 ) {
				$tool_choice_param['type'] = 'tool';
				$tool_choice_param['name'] = $allowed_function_names[0];
			}
		}

		return $tool_choice_param;
	}
}
