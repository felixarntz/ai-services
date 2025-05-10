<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Text_Generation_Model
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\File_Data_Part;
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
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_System_Instruction_Trait;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_Text_Generation_Config_Trait;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_Tool_Config_Trait;
use Felix_Arntz\AI_Services\Services\Traits\Model_Param_Tools_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_API_Client_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Chat_History_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use Generator;
use InvalidArgumentException;

/**
 * Class representing an OpenAI text generation AI model.
 *
 * @since 0.1.0
 * @since 0.5.0 Renamed from `OpenAI_AI_Model`.
 */
class OpenAI_AI_Text_Generation_Model extends Abstract_AI_Model implements With_API_Client, With_Text_Generation, With_Chat_History, With_Function_Calling, With_Multimodal_Input {
	use With_API_Client_Trait;
	use With_Text_Generation_Trait;
	use With_Chat_History_Trait;
	use Model_Param_Text_Generation_Config_Trait;
	use Model_Param_Tool_Config_Trait;
	use Model_Param_Tools_Trait;
	use Model_Param_System_Instruction_Trait;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
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

		$this->set_text_generation_config_from_model_params( $model_params );
		$this->set_tool_config_from_model_params( $model_params );
		$this->set_tools_from_model_params( $model_params );
		$this->set_system_instruction_from_model_params( $model_params );

		$this->set_request_options( $request_options );
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
			'chat/completions',
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
			'chat/completions',
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
		if ( $this->get_system_instruction() ) {
			$contents = array_merge( array( $this->get_system_instruction() ), $contents );
		}

		$transformers = self::get_content_transformers();

		$params = array(
			'messages' => array_map(
				static function ( Content $content ) use ( $transformers ) {
					return Transformer::transform_content( $content, $transformers );
				},
				$contents
			),
		);

		if ( $this->get_tools() ) {
			$params['tools'] = $this->prepare_tools_param( $this->get_tools() );
		}

		if ( $this->get_tool_config() ) {
			$params['tool_choice'] = $this->prepare_tool_choice_param( $this->get_tool_config() );
		}

		$generation_config = $this->get_text_generation_config();
		if ( $generation_config ) {
			$params = Transformer::transform_generation_config_params(
				array_merge( $generation_config->get_additional_args(), $params ),
				$generation_config,
				self::get_generation_config_transformers()
			);
		}

		return $params;
	}

	/**
	 * Extracts the candidates with content from the response.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string, mixed> $response_data         The response data.
	 * @param ?Candidates          $prev_chunk_candidates The candidates from the previous chunk in case of a streaming
	 *                                                    response, or null.
	 * @return Candidates The candidates with content parts.
	 *
	 * @throws Generative_AI_Exception Thrown if the response does not have any candidates with content.
	 */
	private function get_response_candidates( array $response_data, ?Candidates $prev_chunk_candidates = null ): Candidates {
		if ( null === $prev_chunk_candidates ) {
			// Regular (non-streaming) response, or first chunk of a streaming response.
			if ( ! isset( $response_data['choices'] ) ) {
				throw $this->get_api_client()->create_missing_response_key_exception( 'choices' );
			}

			$other_data = $response_data;
			unset( $other_data['choices'] );

			$candidates = new Candidates();
			foreach ( $response_data['choices'] as $index => $choice_data ) {
				if ( isset( $choice_data['delta'] ) && ! isset( $choice_data['message'] ) ) {
					$choice_data['message'] = $choice_data['delta'];
					unset( $choice_data['delta'] );
				}

				$other_choice_data = $choice_data;
				unset( $other_choice_data['message'] );

				$candidates->add_candidate(
					new Candidate(
						$this->prepare_choice_content( $choice_data, $index ),
						array_merge( $other_choice_data, $other_data )
					)
				);
			}

			return $candidates;
		}

		// Subsequent chunk of a streaming response.
		$candidates_data = $this->merge_candidates_chunk(
			$prev_chunk_candidates->to_array(),
			$response_data
		);

		return Candidates::from_array( $candidates_data );
	}

	/**
	 * Merges a streaming response chunk with the previous candidates data.
	 *
	 * @since 0.3.0
	 *
	 * @param array<string, mixed> $candidates_data The candidates data from the previous chunk.
	 * @param array<string, mixed> $chunk_data      The response chunk data.
	 * @return array<string, mixed> The merged candidates data.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function merge_candidates_chunk( array $candidates_data, array $chunk_data ): array {
		if ( ! isset( $chunk_data['choices'] ) ) {
			throw $this->get_api_client()->create_missing_response_key_exception( 'choices' );
		}

		$other_data = $chunk_data;
		unset( $other_data['choices'] );

		foreach ( $chunk_data['choices'] as $index => $choice_data ) {
			if ( isset( $choice_data['delta']['content'] ) ) {
				$candidates_data[ $index ]['content']['parts'][0]['text'] = $choice_data['delta']['content'];
			} else {
				// If there was a previous content block, ensure it is ends in a double newline.
				if (
					isset( $choice_data['finish_reason'] ) &&
					'stop' === $choice_data['finish_reason'] &&
					'' !== $candidates_data[ $index ]['content']['parts'][0]['text'] &&
					! str_ends_with( $candidates_data[ $index ]['content']['parts'][0]['text'], "\n\n" )
				) {
					$text_suffix = str_ends_with( $candidates_data[ $index ]['content']['parts'][0]['text'], "\n" ) ? "\n" : "\n\n";
				} else {
					$text_suffix = '';
				}
				$candidates_data[ $index ]['content']['parts'][0]['text'] = $text_suffix;
			}
			unset( $choice_data['delta'] );

			$candidates_data[ $index ] = array_merge( $candidates_data[ $index ], $choice_data, $other_data );
		}

		return $candidates_data;
	}

	/**
	 * Transforms a given choice from the API response into a Content instance.
	 *
	 * @since 0.3.0
	 *
	 * @param array<string, mixed> $choice_data The API response candidate data.
	 * @param int                  $index       The index of the choice in the response.
	 * @return Content The Content instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_choice_content( array $choice_data, int $index ): Content {
		if ( ! isset( $choice_data['message'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw $this->get_api_client()->create_missing_response_key_exception( "choices.{$index}.message" );
		}

		$role = isset( $choice_data['message']['role'] ) && 'user' === $choice_data['message']['role']
			? Content_Role::USER
			: Content_Role::MODEL;

		return new Content(
			$role,
			$this->prepare_choice_content_parts( $choice_data )
		);
	}

	/**
	 * Transforms a given choice from the API response into a Parts instance.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $choice_data The API response candidate data.
	 * @return Parts The Parts instance.
	 *
	 * @throws Generative_AI_Exception Thrown if the response is invalid.
	 */
	private function prepare_choice_content_parts( array $choice_data ): Parts {
		$parts = array();
		if ( isset( $choice_data['message']['content'] ) && is_string( $choice_data['message']['content'] ) ) {
			$parts[] = array( 'text' => $choice_data['message']['content'] );
		}
		if ( isset( $choice_data['message']['tool_calls'] ) && is_array( $choice_data['message']['tool_calls'] ) ) {
			foreach ( $choice_data['message']['tool_calls'] as $tool_call ) {
				if ( ! isset( $tool_call['type'] ) || 'function' !== $tool_call['type'] || ! isset( $tool_call['function'] ) ) {
					throw $this->get_api_client()->create_response_exception(
						'The response includes a tool of an unexpected type.'
					);
				}
				$parts[] = array(
					'functionCall' => array(
						'id'   => $tool_call['id'],
						'name' => $tool_call['function']['name'],
						'args' => is_string( $tool_call['function']['arguments'] )
							? json_decode( $tool_call['function']['arguments'], true )
							: $tool_call['function']['arguments'],
					),
				);
			}
		}
		if ( count( $parts ) === 0 ) {
			throw $this->get_api_client()->create_response_exception(
				'The response includes unexpected content.'
			);
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
			'role'         => static function ( Content $content ) {
				// Special case of a function response.
				$parts = $content->get_parts();
				if ( count( $parts ) === 1 && $parts->get( 0 ) instanceof Function_Response_Part ) {
					return 'tool';
				}

				if ( $content->get_role() === Content_Role::MODEL ) {
					return 'assistant';
				}
				if ( $content->get_role() === Content_Role::SYSTEM ) {
					return 'system';
				}
				return 'user';
			},
			'content'      => static function ( Content $content ) {
				// Special case of a function response.
				$parts = $content->get_parts();
				if ( count( $parts ) === 1 && $parts->get( 0 ) instanceof Function_Response_Part ) {
					$response = $parts->get( 0 )->get_response();
					return json_encode( $response ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				}

				$parts = array();
				foreach ( $content->get_parts() as $part ) {
					if ( $part instanceof Text_Part ) {
						$parts[] = array(
							'type' => 'text',
							'text' => $part->get_text(),
						);
					} elseif ( $part instanceof Inline_Data_Part ) {
						$mime_type = $part->get_mime_type();
						if ( str_starts_with( $mime_type, 'image/' ) ) {
							$parts[] = array(
								'type'      => 'image_url',
								'image_url' => array(
									'url' => $part->get_base64_data(),
								),
							);
						} elseif ( str_starts_with( $mime_type, 'audio/' ) ) {
							$parts[] = array(
								'type'        => 'input_audio',
								'input_audio' => array(
									'data'   => $part->get_base64_data(),
									'format' => function_exists( 'wp_get_default_extension_for_mime_type' ) ?
										wp_get_default_extension_for_mime_type( $mime_type ) :
										str_replace( 'audio/', '', $mime_type ),
								),
							);
						} else {
							throw new InvalidArgumentException(
								'The OpenAI API only supports text, image, and audio parts.'
							);
						}
					} elseif ( $part instanceof File_Data_Part ) {
						$mime_type = $part->get_mime_type();
						if ( ! str_starts_with( $mime_type, 'image/' ) ) {
							throw new InvalidArgumentException(
								'The OpenAI API only supports text, image, and audio parts.'
							);
						}
						$parts[] = array(
							'type'      => 'image_url',
							'image_url' => array(
								'url' => $part->get_file_uri(),
							),
						);
					} else { // phpcs:ignore Universal.ControlStructures.DisallowLonelyIf.Found

						/*
						 * Special cases: Function call parts are handled as part of a separate `tool_calls` key, and
						 * function response parts are are only supported as the only content of a message. They are
						 * handled as a special case above.
						 */
						if ( $part instanceof Function_Response_Part ) {
							throw new InvalidArgumentException(
								'The OpenAI API only allows a single function response, and it has to be the only content of the message.'
							);
						} elseif ( ! $part instanceof Function_Call_Part ) {
							throw new InvalidArgumentException(
								'The OpenAI API only supports text, image, and audio parts.'
							);
						}
					}
				}
				return $parts;
			},
			'tool_calls'   => static function ( Content $content ) {
				// Special key that only applies in case function calls are present.
				$tool_calls = array();
				foreach ( $content->get_parts() as $part ) {
					if ( $part instanceof Function_Call_Part ) {
						$tool_calls[] = array(
							'type'     => 'function',
							'id'       => $part->get_id(),
							'function' => array(
								'name'      => $part->get_name(),
								'arguments' => json_encode( $part->get_args() ), // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
							),
						);
					}
				}
				if ( count( $tool_calls ) > 0 ) {
					return $tool_calls;
				}
				return null;
			},
			'tool_call_id' => static function ( Content $content ) {
				// Special key that only applies in case of a function response.
				$parts = $content->get_parts();
				if ( count( $parts ) === 1 && $parts->get( 0 ) instanceof Function_Response_Part ) {
					return $parts->get( 0 )->get_id();
				}
				return null;
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
			'stop'                  => static function ( Text_Generation_Config $config ) {
				return $config->get_stop_sequences();
			},
			'response_format'       => static function ( Text_Generation_Config $config ) {
				if ( $config->get_response_mime_type() === 'application/json' ) {
					$schema = $config->get_response_schema();
					if ( $schema ) {
						return array(
							'type'        => 'json_schema',
							'json_schema' => $schema,
						);
					}
					return array( 'type' => 'json_object' );
				}
				return array();
			},
			'n'                     => static function ( Text_Generation_Config $config ) {
				return $config->get_candidate_count();
			},
			'max_completion_tokens' => static function ( Text_Generation_Config $config ) {
				return $config->get_max_output_tokens();
			},
			'temperature'           => static function ( Text_Generation_Config $config ) {
				return $config->get_temperature();
			},
			'top_p'                 => static function ( Text_Generation_Config $config ) {
				return $config->get_top_p();
			},
			'presence_penalty'      => static function ( Text_Generation_Config $config ) {
				return $config->get_presence_penalty();
			},
			'frequency_penalty'     => static function ( Text_Generation_Config $config ) {
				return $config->get_frequency_penalty();
			},
			'logprobs'              => static function ( Text_Generation_Config $config ) {
				return $config->get_response_logprobs();
			},
			'top_logprobs'          => static function ( Text_Generation_Config $config ) {
				return $config->get_logprobs();
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
					'Invalid tool: Only function declarations tools are supported.'
				);
			}

			$function_declarations = $tool->get_function_declarations();
			foreach ( $function_declarations as $declaration ) {
				$tools_param[] = array(
					'type'     => 'function',
					'function' => array_filter(
						array(
							'name'        => $declaration['name'],
							'description' => $declaration['description'] ?? null,
							'parameters'  => $declaration['parameters'] ?? null,
							'strict'      => true,
						)
					),
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
		// Either 'auto' or 'any'.
		$tool_choice_param = $tool_config->get_function_call_mode() === 'any' ? 'required' : 'auto';

		if ( 'required' === $tool_choice_param ) {
			// If one specific function must be called, the parameter needs to be an object, otherwise a string.
			$allowed_function_names = $tool_config->get_allowed_function_names();
			if ( count( $allowed_function_names ) === 1 ) {
				$tool_choice_param = array(
					'type'     => 'function',
					'function' => array( 'name' => $allowed_function_names[0] ),
				);
			}
		}

		return $tool_choice_param;
	}
}
