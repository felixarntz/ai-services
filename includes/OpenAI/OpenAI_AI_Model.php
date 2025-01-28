<?php
/**
 * Class Felix_Arntz\AI_Services\OpenAI\OpenAI_AI_Model
 *
 * @since 0.1.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\OpenAI;

use Felix_Arntz\AI_Services\Services\API\Enums\Content_Role;
use Felix_Arntz\AI_Services\Services\API\Types\Candidate;
use Felix_Arntz\AI_Services\Services\API\Types\Candidates;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Parts;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\File_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Call_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Function_Response_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Tool_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;
use Felix_Arntz\AI_Services\Services\API\Types\Tools\Function_Declarations_Tool;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\With_Chat_History;
use Felix_Arntz\AI_Services\Services\Contracts\With_Multimodal_Input;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Traits\With_Chat_History_Trait;
use Felix_Arntz\AI_Services\Services\Traits\With_Text_Generation_Trait;
use Felix_Arntz\AI_Services\Services\Util\Formatter;
use Felix_Arntz\AI_Services\Services\Util\Transformer;
use Generator;
use InvalidArgumentException;

/**
 * Class representing an OpenAI AI model.
 *
 * @since 0.1.0
 */
class OpenAI_AI_Model implements Generative_AI_Model, With_Multimodal_Input, With_Chat_History {
	use With_Text_Generation_Trait;
	use With_Chat_History_Trait;

	/**
	 * The OpenAI AI API instance.
	 *
	 * @since 0.1.0
	 * @var OpenAI_AI_API_Client
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
	 * The tools available to use for the model.
	 *
	 * @since n.e.x.t
	 * @var Tools|null
	 */
	private $tools;

	/**
	 * The tool configuration, if applicable.
	 *
	 * @since n.e.x.t
	 * @var Tool_Config|null
	 */
	private $tool_config;

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
		$this->api             = $api;
		$this->request_options = $request_options;

		$this->model = $model;

		$data_obj_params = array(
			array(
				'param_key'     => 'tools',
				'property_name' => 'tools',
				'class_name'    => Tools::class,
			),
			array(
				'param_key'     => 'toolConfig',
				'property_name' => 'tool_config',
				'class_name'    => Tool_Config::class,
			),
			array(
				'param_key'     => 'generationConfig',
				'property_name' => 'generation_config',
				'class_name'    => Generation_Config::class,
			),
		);
		foreach ( $data_obj_params as $data_obj_param ) {
			$param_key     = $data_obj_param['param_key'];
			$property_name = $data_obj_param['property_name'];
			$class_name    = $data_obj_param['class_name'];
			if ( isset( $model_params[ $param_key ] ) ) {
				if ( $model_params[ $param_key ] instanceof $class_name ) {
					$this->$property_name = $model_params[ $param_key ];
				} else {
					$this->$property_name = $class_name::from_array( $model_params[ $param_key ] );
				}
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
		if ( $this->system_instruction ) {
			$contents = array_merge( array( $this->system_instruction ), $contents );
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
				throw $this->api->create_missing_response_key_exception( 'choices' );
			}

			$other_data = $response_data;
			unset( $other_data['choices'] );

			$candidates = new Candidates();
			foreach ( $response_data['choices'] as $index => $choice_data ) {
				if ( isset( $choice_data['delta'] ) && ! isset( $choice_data['message'] ) ) {
					$choice_data['message'] = $choice_data['delta'];
					unset( $choice_data['delta'] );
				}

				$candidates->add_candidate(
					new Candidate(
						$this->prepare_choice_content( $choice_data, $index ),
						array_merge( $choice_data, $other_data )
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
			throw $this->api->create_missing_response_key_exception( 'choices' );
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
			throw $this->api->create_missing_response_key_exception( "choices.{$index}.message" );
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
	 * @since n.e.x.t
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
					throw $this->api->create_response_exception(
						// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
						__( 'The response includes a tool of an unexpected type.', 'ai-services' )
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
			throw $this->api->create_response_exception(
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				__( 'The response includes unexpected content.', 'ai-services' )
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
					return wp_json_encode( $response );
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
									'format' => wp_get_default_extension_for_mime_type( $mime_type ),
								),
							);
						} else {
							throw new InvalidArgumentException(
								esc_html__( 'The OpenAI API only supports text, image, and audio parts.', 'ai-services' )
							);
						}
					} elseif ( $part instanceof File_Data_Part ) {
						$mime_type = $part->get_mime_type();
						if ( ! str_starts_with( $mime_type, 'image/' ) ) {
							throw new InvalidArgumentException(
								esc_html__( 'The OpenAI API only supports text, image, and audio parts.', 'ai-services' )
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
								esc_html__( 'The OpenAI API only allows a single function response, and it has to be the only content of the message.', 'ai-services' )
							);
						} elseif ( ! $part instanceof Function_Call_Part ) {
							throw new InvalidArgumentException(
								esc_html__( 'The OpenAI API only supports text, image, and audio parts.', 'ai-services' )
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
								'arguments' => wp_json_encode( $part->get_args() ),
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
			'stop'                  => static function ( Generation_Config $config ) {
				return $config->get_stop_sequences();
			},
			'response_format'       => static function ( Generation_Config $config ) {
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
			'n'                     => static function ( Generation_Config $config ) {
				return $config->get_candidate_count();
			},
			'max_completion_tokens' => static function ( Generation_Config $config ) {
				return $config->get_max_output_tokens();
			},
			'temperature'           => static function ( Generation_Config $config ) {
				return $config->get_temperature();
			},
			'top_p'                 => static function ( Generation_Config $config ) {
				return $config->get_top_p();
			},
			'presence_penalty'      => static function ( Generation_Config $config ) {
				return $config->get_presence_penalty();
			},
			'frequency_penalty'     => static function ( Generation_Config $config ) {
				return $config->get_frequency_penalty();
			},
			'logprobs'              => static function ( Generation_Config $config ) {
				return $config->get_response_logprobs();
			},
			'top_logprobs'          => static function ( Generation_Config $config ) {
				return $config->get_logprobs();
			},
		);
	}

	/**
	 * Prepares the API request tools parameter for the model.
	 *
	 * @since n.e.x.t
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
	 * @since n.e.x.t
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
